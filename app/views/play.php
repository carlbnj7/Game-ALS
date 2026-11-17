<div class="play" id="play" data-s="<?= e($s) ?>" data-l="<?= (int)$l ?>" data-a="<?= (int)$a ?>" data-api="<?= e(url('api')) ?>" data-back="<?= e(url('strand', ['s' => $s])) ?>">
  <header class="play-top">
    <div class="pt-row">
      <a class="icon-btn" href="<?= url('strand', ['s' => $s]) ?>" aria-label="Leave activity" id="leave"><?= icon('x', 18) ?></a>
      <div class="pt-title"><small class="c-text-<?= e(strtolower($s)) ?>"><?= e(STRANDS[$s][0] . ' ' . mb_strtoupper(STRANDS[$s][1])) ?> · LEVEL <?= (int)$l ?></small><b>Activity <?= (int)$a ?> <span id="itemNo"></span></b></div>
      <div class="hearts" id="hearts" aria-live="polite"></div>
      <button type="button" class="icon-btn" data-theme-toggle aria-label="Switch light or dark"><?= icon('moon', 17) ?></button>
    </div>
    <div class="bar" id="bar" aria-hidden="true"></div>
    <div class="pt-meta">
      <span class="streak" id="streak" title="5 correct in a row gives one more life"><?= icon('flame', 14) ?><span class="dots"></span><em></em></span>
      <span class="tally" id="tally"></span>
    </div>
  </header>

  <main class="play-body" id="body">
    <div class="loading" id="loading"><span class="spin"></span>Loading…</div>
    <article class="qcard" id="qcard" hidden>
      <section class="passage" id="passage" hidden>
        <div class="ph"><?= icon('book', 16) ?><b>Read first</b><button type="button" class="linkish" id="pToggle">Show all</button></div>
        <div class="ptext" id="ptext"></div>
      </section>
      <figure class="figs" id="figs" hidden></figure>
      <h2 class="qtext" id="qtext"></h2>
      <p class="qhint muted small" id="qhelp"></p>
      <div class="opts" id="opts" role="radiogroup" aria-labelledby="qtext"></div>
    </article>
  </main>

  <footer class="play-foot" id="foot">
    <button type="button" class="btn hint" id="hintBtn"><?= icon('bulb', 18) ?><span>Hint</span><em id="hintN">3</em></button>
    <button type="button" class="btn primary big grow" id="checkBtn" disabled>Check answer</button>
  </footer>

  <div class="sheet" id="sheet" hidden>
    <div class="sheet-in" role="dialog" aria-modal="true" aria-labelledby="fbTitle">
      <div class="fb-head"><span class="fb-ic" id="fbIc"></span><div><h3 id="fbTitle"></h3><p id="fbSub"></p></div></div>
      <div class="fb-ans" id="fbAns"></div>
      <div class="fb-expl" id="fbExpl"></div>
      <button type="button" class="btn big grow" id="nextBtn">Continue</button>
    </div>
  </div>

  <div class="toast" id="toast" hidden></div>

  <section class="result" id="result" hidden>
    <div class="res-card">
      <div class="ring" id="ring"><svg viewBox="0 0 120 120"><circle cx="60" cy="60" r="52" class="track"/><circle cx="60" cy="60" r="52" class="val" id="ringVal"/></svg><b id="resPct"></b></div>
      <h2 id="resTitle"></h2>
      <p class="muted" id="resSub"></p>
      <div class="res-stats"><div><b id="rsCorrect"></b><span>Correct</span></div><div><b id="rsNeed"></b><span>Needed</span></div><div><b id="rsStreak"></b><span>Best streak</span></div></div>
      <div class="res-certs" id="resCerts" hidden></div>
      <div class="res-btns">
        <a class="btn primary big" id="resNext" href="#"></a>
        <button type="button" class="btn ghost big" id="resRetry"><?= icon('refresh', 16) ?> Play again</button>
        <a class="btn linkish" href="<?= url('strand', ['s' => $s]) ?>">Back to levels</a>
      </div>
    </div>
  </section>

  <div class="zoom" id="zoom" hidden><img id="zoomImg" alt=""><button type="button" class="icon-btn" id="zoomX" aria-label="Close"><?= icon('x', 18) ?></button></div>
</div>
