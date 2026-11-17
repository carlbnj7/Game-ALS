<a class="back" href="<?= url('learners') ?>"><?= icon('back', 16) ?> Learners</a>
<div class="card narrow wide">
  <h2>Upload learners from AF3 or a list</h2>
  <p class="muted">Upload the AF3 form saved as <b>.xlsx</b>, or a <b>.csv</b> file. The first sheet is read. Columns are found by their header names: <i>LRN, Last Name, First Name, Middle Name, Sex, Birthdate, Level</i>, or one <i>Name</i> column written as “Last, First M.”.</p>
  <?php if ($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="form grid2"><?= csrf_field() ?>
    <label>CLC<select name="clc_id" required><?php foreach ($clcs as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
    <label>Grade level when the file does not say<select name="grade"><?php foreach (GRADES as $k => $g): ?><option value="<?= $k ?>"><?= e($g) ?></option><?php endforeach; ?></select></label>
    <label class="span2 drop"><?= icon('upload', 22) ?><span>Choose the AF3 .xlsx or .csv file</span><input type="file" name="file" accept=".xlsx,.csv" required></label>
    <button class="btn primary"><?= icon('upload', 16) ?> Upload and make accounts</button>
  </form>
</div>
<?php if ($result): ?>
<div class="card">
  <h3><?= count($result['made']) ?> account<?= count($result['made']) == 1 ? '' : 's' ?> made<?= $result['skipped'] ? ', ' . count($result['skipped']) . ' skipped' : '' ?></h3>
  <p class="muted small">Columns found: <?= e(implode(', ', $result['cols'])) ?></p>
  <?php if ($result['made']): ?><p><a class="btn primary" href="<?= url('slips') ?>"><?= icon('print', 16) ?> Print sign-in slips</a></p>
  <table class="tbl"><thead><tr><th>Name</th><th>Username</th><th>Grade</th></tr></thead><tbody><?php foreach ($result['made'] as [$n, $un, $pw, $g]): ?><tr><td><?= e($n) ?></td><td><?= e($un) ?></td><td><?= e($g) ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
  <?php if ($result['skipped']): ?><details><summary>Skipped</summary><ul><?php foreach ($result['skipped'] as $s): ?><li><?= e($s) ?></li><?php endforeach; ?></ul></details><?php endif; ?>
</div>
<?php endif; ?>
