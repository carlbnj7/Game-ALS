<a class="back" href="<?= url('learners', ['clc' => $l['clc_id']]) ?>"><?= icon('back', 16) ?> Learners</a>
<section class="hello">
  <div><p class="eyebrow"><?= e(GRADES[$l['grade'] ?: 'ELEM']) ?> · <?= e($l['als_id']) ?><?= $l['setup_done'] ? ' · ' . e($l['username']) : '' ?></p><h1><?= e(full_name($l)) ?></h1><p class="muted">Mastered means a best score of 75% or more in that activity.</p></div>
  <div class="stats3"><div><b><?= number_format($points) ?></b><span>Points</span></div><div><b><?= count($mastered) ?><small>/63</small></b><span>Mastered</span></div><div><b><?= count($certs) ?></b><span>Certificates</span></div></div>
</section>
<h2 class="sec">Progress by strand</h2>
<div class="card scroll-x"><table class="tbl grid-tbl">
<thead><tr><th>Strand</th><?php for ($lv = 1; $lv <= 3; $lv++) for ($a = 1; $a <= 3; $a++): ?><th>L<?= $lv ?>·A<?= $a ?></th><?php endfor; ?></tr></thead><tbody>
<?php foreach (STRANDS as $s => $st): ?><tr><td><span class="pill c-<?= e(strtolower($s)) ?>"><?= e($st[0]) ?></span> <?= e($st[1]) ?></td>
<?php for ($lv = 1; $lv <= 3; $lv++) for ($a = 1; $a <= 3; $a++): $p = $pm["$s|$lv|$a"] ?? null; ?>
<td class="sc <?= $p ? ($p['passed'] ? 'ok' : 'bad') : '' ?>"><?= $p ? (int)round((float)$p['best_score']) . '%' : '·' ?></td>
<?php endfor; ?></tr><?php endforeach; ?>
</tbody></table></div>
<div class="two">
  <div class="card"><h3><?= icon('star', 18) ?> Mastered</h3><?php if (!$mastered): ?><p class="muted">None yet.</p><?php endif; ?><ul class="mlist"><?php foreach ($mastered as $m): ?><li><span><?= e($m['label']) ?></span><b class="ok"><?= (int)round($m['score']) ?>%</b></li><?php endforeach; ?></ul></div>
  <div class="card"><h3><?= icon('flame', 18) ?> Least mastered</h3><?php if (!$least): ?><p class="muted">Nothing below 75% so far.</p><?php endif; ?><ul class="mlist"><?php foreach ($least as $m): ?><li><span><?= e($m['label']) ?> · <?= $m['tries'] ?> tr<?= $m['tries'] == 1 ? 'y' : 'ies' ?></span><b class="bad"><?= (int)round($m['score']) ?>%</b></li><?php endforeach; ?></ul></div>
</div>
<?php if ($missed): ?>
<div class="card"><h3>Questions missed most</h3><ul class="mlist"><?php foreach ($missed as $m): ?><li><span><b><?= e(STRANDS[$m['strand']][0] . ' L' . $m['level'] . ' #' . $m['num']) ?></b> <?= e($m['q']) ?>…</span><b class="bad"><?= (int)$m['n'] ?>×</b></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<?php if ($certs): ?><div class="card"><h3>Certificates</h3><div class="row-btns"><?php foreach ($certs as $c): ?><a class="btn sm" href="<?= url('cert', ['code' => $c['code']]) ?>"><?= icon('award', 14) ?> <?= $c['type'] === 'readiness' ? 'Readiness' : e(STRANDS[$c['strand']][0] . ' ' . STRANDS[$c['strand']][1] . ' L' . $c['level']) ?></a><?php endforeach; ?></div></div><?php endif; ?>
<details class="card add"><summary><?= icon('edit', 18) ?> Edit details</summary>
<form method="post" class="form grid2"><?= csrf_field() ?><input type="hidden" name="do" value="save">
  <label>First name<input name="first_name" value="<?= e($l['first_name']) ?>" required></label>
  <label>Middle name<input name="middle_name" value="<?= e($l['middle_name']) ?>"></label>
  <label>Last name<input name="last_name" value="<?= e($l['last_name']) ?>" required></label>
  <label>LRN<input name="lrn" value="<?= e($l['lrn']) ?>"></label>
  <label>Sex<select name="sex"><option value="">—</option><option value="M" <?= $l['sex'] === 'M' ? 'selected' : '' ?>>Male</option><option value="F" <?= $l['sex'] === 'F' ? 'selected' : '' ?>>Female</option></select></label>
  <label>Birthdate<input type="date" name="birthdate" value="<?= e($l['birthdate'] ?? '') ?>"></label>
  <label>Grade level<select name="grade"><?php foreach (GRADES as $k => $g): ?><option value="<?= $k ?>" <?= $l['grade'] === $k ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?></select></label>
  <label>CLC<select name="clc_id"><?php foreach ($clcs as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $l['clc_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
  <button class="btn primary">Save</button>
</form></details>
<div class="row-btns">
  <form method="post" data-confirm="Make a new password for this learner?"><?= csrf_field() ?><input type="hidden" name="do" value="reset"><button class="btn"><?= icon('key', 16) ?> New password</button></form>
  <form method="post" data-confirm="Clear this learner's PIN? They make a new one on their next sign-in."><?= csrf_field() ?><input type="hidden" name="do" value="pin"><button class="btn"><?= icon('pin', 16) ?> Reset PIN</button></form>
  <a class="btn" href="<?= url('passes', ['q' => $l['als_id']]) ?>"><?= icon('qr', 16) ?> QR pass</a>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="toggle"><button class="btn ghost"><?= $l['active'] ? 'Turn off account' : 'Turn on account' ?></button></form>
</div>
