<a class="back" href="<?= url('learners') ?>"><?= icon('back', 16) ?> Learners</a>
<div class="card narrow wide"><h2>Add a learner</h2>
<?php if ($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
<?php if (!$clcs): ?><p class="flash err">You have no CLC yet. Ask the admin to assign one.</p><?php endif; ?>
<form method="post" class="form grid2"><?= csrf_field() ?>
  <label>First name<input name="first_name" required value="<?= e($_POST['first_name'] ?? '') ?>"></label>
  <label>Middle name<input name="middle_name" value="<?= e($_POST['middle_name'] ?? '') ?>"></label>
  <label>Last name<input name="last_name" required value="<?= e($_POST['last_name'] ?? '') ?>"></label>
  <label>LRN (if any)<input name="lrn" inputmode="numeric" value="<?= e($_POST['lrn'] ?? '') ?>"></label>
  <label>Sex<select name="sex"><option value="">—</option><option value="M">Male</option><option value="F">Female</option></select></label>
  <label>Birthdate<input type="date" name="birthdate"></label>
  <label>Grade level<select name="grade"><?php foreach (GRADES as $k => $g): ?><option value="<?= $k ?>"><?= e($g) ?></option><?php endforeach; ?></select></label>
  <label>CLC<select name="clc_id" required><?php foreach ($clcs as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
  <div class="row-btns span2"><button class="btn primary">Make account</button><button class="btn" name="again" value="1">Make and add another</button></div>
</form>
<p class="muted small">The app makes the learner's ALS ID and a starting password. On first sign-in the learner picks their own username, password and 4-digit PIN.</p>
</div>
