<?php
$distSelect = function (?int $sel, string $form = '') use ($muns, $dists): string {
    $h = '<select name="district_id"' . ($form ? ' form="' . e($form) . '"' : '') . ' aria-label="District"><option value="0">Choose district</option>';
    $by = []; foreach ($dists as $d) $by[$d['municipality']][] = $d;
    foreach ($by as $m => $ds) { $h .= '<optgroup label="' . e($m) . '">'; foreach ($ds as $d) $h .= '<option value="' . (int)$d['id'] . '"' . ($sel === (int)$d['id'] ? ' selected' : '') . '>' . e($d['name'] . ' · ' . $m) . '</option>'; $h .= '</optgroup>'; }
    return $h . '</select>';
};
?>
<div class="page-head"><div><h1>Community Learning Centers</h1><p class="muted">Each CLC belongs to a district. Add municipalities and districts on the <a href="<?= url('places') ?>">Municipalities</a> page.</p></div>
<div class="row-btns"><a class="btn" href="<?= url('places') ?>"><?= icon('city', 16) ?> Municipalities</a></div></div>
<?php if (!$dists): ?><div class="flash warn">No districts yet. <a href="<?= url('places') ?>">Add a municipality and its districts</a> first, then choose them here.</div><?php endif; ?>
<form method="post" class="card form inline-form"><?= csrf_field() ?><input type="hidden" name="do" value="add">
  <label>CLC name<input name="name" required></label><label>District<?= $distSelect(null) ?></label><button class="btn primary"><?= icon('plus', 16) ?> Add CLC</button>
</form>
<form class="filters" method="get"><input type="hidden" name="p" value="clcs"><?php require ROOT . '/app/views/_places.php'; ?><button class="btn">Show</button></form>
<div class="card"><table class="tbl">
<thead><tr><th>CLC</th><th>District · Municipality</th><th>Teachers</th><th>Learners</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): $fid = 'f' . (int)$r['id']; ?>
<tr>
  <td><input form="<?= $fid ?>" name="name" value="<?= e($r['name']) ?>" required aria-label="CLC name"></td>
  <td><?= $distSelect($r['district_id'] !== null ? (int)$r['district_id'] : null, $fid) ?></td>
  <td class="small"><?= e($r['teachers'] ?: '—') ?></td><td><?= (int)$r['learners'] ?></td>
  <td class="acts-cell">
    <form id="<?= $fid ?>" method="post"><?= csrf_field() ?><input type="hidden" name="do" value="save"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="mun" value="<?= (int)$f['mun'] ?>"><input type="hidden" name="fdist" value="<?= (int)$f['dist'] ?>"><button class="btn sm">Save</button></form>
    <form method="post" data-confirm="Remove this CLC?"><?= csrf_field() ?><input type="hidden" name="do" value="del"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn sm ghost">Remove</button></form>
  </td>
</tr>
<?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="muted">No CLCs here yet.</td></tr><?php endif; ?>
</tbody></table></div>
