(function () {
  'use strict';
  var root = document.getElementById('play'); if (!root) return;
  var $ = function (id) { return document.getElementById(id); };
  var csrf = document.body.getAttribute('data-csrf');
  var ctx = { s: root.dataset.s, l: +root.dataset.l, a: +root.dataset.a };
  var st = null, pick = null, busy = false, removed = [], answered = false;
  var SVG = {
    heart: '<svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 20s-7-4.5-9-9a4.5 4.5 0 0 1 9-2 4.5 4.5 0 0 1 9 2c-2 4.5-9 9-9 9z"/></svg>',
    ok: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>',
    no: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>',
    zoom: '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4M11 8v6M8 11h6"/></svg>'
  };

  function api(body) {
    body.s = ctx.s; body.l = ctx.l; body.a = ctx.a;
    return fetch(root.dataset.api, {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF': csrf },
      body: JSON.stringify(body)
    }).then(function (r) {
      return r.json().catch(function () { return { error: 'The server did not answer properly.' }; }).then(function (j) { j._status = r.status; return j; });
    }).catch(function () { return { error: 'No internet connection. Check your connection and try again.' }; });
  }
  function el(tag, cls, txt) { var e = document.createElement(tag); if (cls) e.className = cls; if (txt != null) e.textContent = txt; return e; }
  // Write text into an element, showing fractions like 3/4 as stacked fractions.
  function fill(node, text) {
    node.textContent = '';
    String(text || '').split(/(\d+\/\d+)/).forEach(function (part) {
      var m = /^(\d+)\/(\d+)$/.exec(part);
      if (m && m[2] !== '0' && part.length < 9) {
        var f = el('span', 'frac'); f.appendChild(el('span', null, m[1])); f.appendChild(el('span', null, m[2]));
        f.setAttribute('aria-label', m[1] + ' over ' + m[2]); node.appendChild(f);
      } else if (part) node.appendChild(document.createTextNode(part));
    });
    return node;
  }
  function mel(tag, cls, txt) { return fill(el(tag, cls), txt); }
  function toast(msg, kind) {
    var t = $('toast'); t.textContent = msg; t.className = 'toast ' + (kind || ''); t.hidden = false;
    clearTimeout(toast._t); toast._t = setTimeout(function () { t.hidden = true; }, 2600);
  }

  function drawTop() {
    var h = $('hearts'); h.innerHTML = '';
    var n = Math.max(st.lives, 3);
    for (var i = 0; i < n; i++) { var s = el('span', 'hrt' + (i < st.lives ? ' on' : '')); s.innerHTML = SVG.heart; h.appendChild(s); }
    h.setAttribute('aria-label', st.lives + ' lives left');
    var bar = $('bar'); bar.innerHTML = '';
    for (var k = 0; k < st.total; k++) {
      var m = st.marks[k], c = 'seg';
      if (m === 1) c += ' ok'; else if (m === 0) c += ' bad'; else if (k === st.pos) c += ' cur';
      bar.appendChild(el('span', c));
    }
    var sd = $('streak'), dots = sd.querySelector('.dots'); dots.innerHTML = '';
    var inRow = st.streak % st.streakGoal;
    if (st.streak > 0 && inRow === 0) inRow = st.streakGoal;
    for (var j = 0; j < st.streakGoal; j++) dots.appendChild(el('i', j < inRow ? 'on' : ''));
    sd.querySelector('em').textContent = st.lives >= st.maxLives ? 'Max lives' : (st.streakGoal - inRow) + ' more for +1 life';
    sd.classList.toggle('hot', inRow >= 3);
    $('tally').textContent = st.correct + ' correct · pass at ' + st.need + ' of ' + st.total;
    if (!answered) $('itemNo').textContent = st.item ? '· ' + st.item.no + ' of ' + st.total : '';
    $('hintN').textContent = st.hints;
    $('hintBtn').disabled = st.hints < 1 || answered || removed.length > 0;
  }

  function drawItem() {
    var it = st.item; pick = null; answered = false; removed = (st.hinted || []).slice();
    $('loading').hidden = true; $('qcard').hidden = false;
    var p = $('passage');
    if (it.passage) {
      p.hidden = false;
      var pt = $('ptext'); pt.innerHTML = '';
      it.passage.split(/\n\n+/).forEach(function (para) { pt.appendChild(mel('p', null, para)); });
      var long = it.passage.length > 380;
      p.classList.toggle('clamp', long); p.classList.remove('open');
      $('pToggle').hidden = !long; $('pToggle').textContent = 'Show all';
    } else p.hidden = true;
    var f = $('figs'); f.innerHTML = '';
    if (it.img.length) {
      f.hidden = false;
      it.img.forEach(function (src) {
        var b = el('button', 'fig'); b.type = 'button'; b.setAttribute('aria-label', 'Make the picture bigger');
        var im = el('img'); im.src = src; im.alt = 'Picture for this question from the module'; im.loading = 'lazy';
        b.appendChild(im); var z = el('span', 'zbadge'); z.innerHTML = SVG.zoom + ' Zoom'; b.appendChild(z);
        b.addEventListener('click', function () { $('zoomImg').src = src; $('zoom').hidden = false; });
        f.appendChild(b);
      });
      f.appendChild(el('figcaption', null, it.imgOnly ? 'Read the question and choices in the picture, then tap your answer below.' : 'Picture from the module.'));
    } else f.hidden = true;
    var qt = it.q;
    if (it.imgOnly) qt = qt.split(/\s+/).filter(function (w) { return !/^[!#$%&'()*+,\-\/:;<=>@\[\]^_`{|}~"]+$/.test(w); }).join(' ');
    fill($('qtext'), qt || 'Look at the picture and choose the correct answer.');
    $('qhelp').textContent = 'Choose one answer.';
    var o = $('opts'); o.innerHTML = '';
    o.classList.toggle('letters', it.imgOnly);
    it.opts.forEach(function (op) {
      var b = el('button', 'opt'); b.type = 'button'; b.dataset.k = op.k;
      b.setAttribute('role', 'radio'); b.setAttribute('aria-checked', 'false');
      b.appendChild(el('span', 'key', op.k));
      if (!it.imgOnly) b.appendChild(mel('span', 'otext', op.t));
      var mk = el('span', 'mark'); b.appendChild(mk);
      if (removed.indexOf(op.k) >= 0) { b.classList.add('gone'); b.disabled = true; }
      b.addEventListener('click', function () { choose(op.k); });
      o.appendChild(b);
    });
    $('checkBtn').disabled = true; $('checkBtn').textContent = 'Check answer';
    drawTop();
    $('body').scrollTop = 0; window.scrollTo(0, 0);
  }

  function choose(k) {
    if (answered || busy || removed.indexOf(k) >= 0) return;
    pick = k;
    [].forEach.call($('opts').children, function (b) {
      var on = b.dataset.k === k; b.classList.toggle('sel', on); b.setAttribute('aria-checked', on ? 'true' : 'false');
    });
    $('checkBtn').disabled = false;
  }

  function hint() {
    if (busy || answered || st.hints < 1 || removed.length) return;
    busy = true;
    api({ do: 'hint', pos: st.pos }).then(function (r) {
      busy = false;
      if (r.error) return handleErr(r);
      removed = r.remove; st.hints = r.hints;
      [].forEach.call($('opts').children, function (b) {
        if (removed.indexOf(b.dataset.k) >= 0) { b.classList.add('gone'); b.disabled = true; if (pick === b.dataset.k) { pick = null; b.classList.remove('sel'); $('checkBtn').disabled = true; } }
      });
      toast('Two wrong choices were taken away.', 'info');
      drawTop();
    });
  }

  function check() {
    if (!pick || busy || answered) return;
    busy = true; $('checkBtn').disabled = true; $('checkBtn').textContent = 'Checking…';
    api({ do: 'answer', pos: st.pos, choice: pick }).then(function (r) {
      busy = false;
      if (r.error) { $('checkBtn').textContent = 'Check answer'; $('checkBtn').disabled = false; return handleErr(r); }
      answered = true;
      var good = r.correct;
      [].forEach.call($('opts').children, function (b) {
        b.disabled = true;
        if (b.dataset.k === r.answer) { b.classList.add('right'); b.querySelector('.mark').innerHTML = SVG.ok; }
        else if (b.dataset.k === pick) { b.classList.add('wrong'); b.querySelector('.mark').innerHTML = SVG.no; }
      });
      var oldLives = st.lives;
      st = r.state; st.item = st.item || null;
      drawTop();
      $('hintBtn').disabled = true;
      if (!good) { var hs = $('hearts').children; if (hs[st.lives]) hs[st.lives].classList.add('pop'); }
      if (navigator.vibrate) try { navigator.vibrate(good ? 25 : [40, 60, 40]); } catch (e) {}
      showFeedback(r, oldLives);
    });
  }

  function showFeedback(r, oldLives) {
    var sh = $('sheet'); sh.className = 'sheet ' + (r.correct ? 'good' : 'bad');
    $('fbIc').innerHTML = r.correct ? SVG.ok : SVG.no;
    var praise = ['Correct!', 'Nice work!', 'That is right!', 'Well done!'];
    $('fbTitle').textContent = r.correct ? praise[Math.floor(Math.random() * praise.length)] : 'Not quite';
    var sub = r.correct ? (r.lifeGained ? '5 in a row. You got one more life!' : (st.streak > 1 ? st.streak + ' correct in a row.' : 'Keep going.')) :
      (st.lives > 0 ? 'You lost one life. ' + st.lives + ' left.' : 'You have no lives left.');
    $('fbSub').textContent = sub;
    var ans = $('fbAns'); ans.innerHTML = '';
    if (!r.correct) {
      ans.appendChild(el('span', 'k', r.answer));
      ans.appendChild(mel('span', null, r.answerText ? r.answerText : 'is the correct answer.'));
      ans.hidden = false;
    } else ans.hidden = true;
    var ex = $('fbExpl'); ex.innerHTML = '';
    if (r.explanation) { ex.appendChild(el('b', null, 'Why: ')); ex.appendChild(mel('span', null, r.explanation)); ex.hidden = false; } else ex.hidden = true;
    $('nextBtn').textContent = r.result ? (r.result.lost ? 'See what happened' : 'See my score') : 'Next question';
    sh.hidden = false;
    $('foot').classList.add('hide');
    setTimeout(function () { $('nextBtn').focus(); }, 60);
    if (r.lifeGained) toast('+1 life! 5 correct in a row.', 'good');
    $('nextBtn').onclick = function () {
      sh.hidden = true; $('foot').classList.remove('hide');
      if (r.result) return showResult(r.result);
      drawItem();
    };
  }

  function showResult(res) {
    $('qcard').hidden = true; $('foot').classList.add('hide');
    var R = $('result'); R.hidden = false; R.className = 'result ' + (res.lost ? 'lost' : (res.passed ? 'pass' : 'fail'));
    var pct = res.score; $('resPct').textContent = pct + '%';
    var C = 2 * Math.PI * 52; var v = $('ringVal');
    v.setAttribute('stroke-dasharray', C.toFixed(1)); v.setAttribute('stroke-dashoffset', C.toFixed(1));
    setTimeout(function () { v.setAttribute('stroke-dashoffset', (C * (1 - pct / 100)).toFixed(1)); }, 80);
    $('resTitle').textContent = res.lost ? 'Out of lives' : (res.passed ? 'You passed!' : 'Almost there');
    $('resSub').textContent = res.lost ? 'This activity starts over from item 1. Use your hints, and read each choice slowly.' :
      (res.passed ? 'Great job. Your best score is saved.' : 'You need ' + st.passMark + '% to pass. Try again — you can do it.');
    $('rsCorrect').textContent = res.correct + '/' + res.total; $('rsNeed').textContent = st.need; $('rsStreak').textContent = res.bestStreak;
    var cc = $('resCerts'); cc.innerHTML = '';
    if (res.certs && res.certs.length) {
      cc.hidden = false;
      res.certs.forEach(function (c) { cc.appendChild(el('div', 'cert-new', 'New: ' + c)); });
    } else cc.hidden = true;
    var nx = $('resNext');
    if (res.passed) { nx.hidden = false; nx.textContent = res.nextLabel; nx.href = res.nextUrl; $('resRetry').className = 'btn ghost big'; }
    else { nx.hidden = true; $('resRetry').className = 'btn primary big'; }
    $('resRetry').lastChild.textContent = res.lost ? ' Start over' : (res.passed ? ' Play again' : ' Try again');
  }

  function restart() {
    $('result').hidden = true; $('loading').hidden = false; $('foot').classList.remove('hide');
    api({ do: 'restart' }).then(load);
  }

  function handleErr(r) {
    if (r.error === 'stale' && r.state) { st = r.state; if (st.item) drawItem(); return; }
    if (r._status === 401) { location.href = 'index.php?p=login'; return; }
    toast(r.error || 'Something went wrong.', 'bad');
  }

  function load(r) {
    if (r.error) { $('loading').textContent = r.error; return; }
    st = r.state;
    if (st.item) drawItem();
  }

  $('hintBtn').addEventListener('click', hint);
  $('checkBtn').addEventListener('click', check);
  $('resRetry').addEventListener('click', restart);
  $('pToggle').addEventListener('click', function () {
    var p = $('passage'), o = p.classList.toggle('open'); this.textContent = o ? 'Show less' : 'Show all';
  });
  $('zoom').addEventListener('click', function (e) { if (e.target.id !== 'zoomImg') $('zoom').hidden = true; });
  document.addEventListener('keydown', function (e) {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    if (!$('zoom').hidden && e.key === 'Escape') { $('zoom').hidden = true; return; }
    if (!$('sheet').hidden) { if (e.key === 'Enter') { e.preventDefault(); $('nextBtn').click(); } return; }
    var k = e.key.toUpperCase(), map = { '1': 'A', '2': 'B', '3': 'C', '4': 'D' };
    if (map[k]) k = map[k];
    if (['A', 'B', 'C', 'D'].indexOf(k) >= 0 && st && st.item && !$('qcard').hidden) choose(k);
    else if (e.key === 'Enter' && pick && !answered) { e.preventDefault(); check(); }
  });
  api({ do: 'state' }).then(load);
})();
