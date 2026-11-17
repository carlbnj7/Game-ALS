<div class="card narrow"><h2>Names on certificates</h2>
<form method="post" class="form"><?= csrf_field() ?>
<?php foreach ($keys as $k => $label): ?><label><?= e($label) ?><input name="<?= e($k) ?>" value="<?= e(setting($k)) ?>"></label><?php endforeach; ?>
<fieldset class="step"><legend>Timer for each question</legend><?php $sec = (int)setting('item_seconds', '60'); ?>
  <label class="ck"><input type="checkbox" name="timer_on" <?= $sec > 0 ? 'checked' : '' ?>> Use a timer</label>
  <label>Seconds for each question (10 to 600)<input type="number" name="item_seconds" min="10" max="600" value="<?= $sec > 0 ? $sec : 60 ?>"></label>
  <small class="muted">When time runs out, the question counts as wrong and takes one life.</small></fieldset>
<button class="btn primary">Save</button></form></div>
