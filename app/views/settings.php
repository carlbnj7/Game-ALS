<div class="card narrow"><h2>Names on certificates</h2>
<form method="post" class="form"><?= csrf_field() ?>
<?php foreach ($keys as $k => $label): ?><label><?= e($label) ?><input name="<?= e($k) ?>" value="<?= e(setting($k)) ?>"></label><?php endforeach; ?>
<button class="btn primary">Save</button></form></div>
