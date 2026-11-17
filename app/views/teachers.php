<div class="page-head"><div><h1>Teachers and admins</h1><p class="muted">The app makes each ALS ID and starter password. On first sign-in they pick their own username, password and 4-digit PIN.</p></div>
<div class="row-btns"><a class="btn" href="<?= url('passes', ['role' => 'teacher']) ?>"><?= icon('qr', 16) ?> QR passes</a></div></div>
<?php if ($shown): ?><div class="pw-slip"><b><?= e($shown[0]) ?></b> <span class="pill"><?= e(ucfirst($shown[3])) ?></span><br><b>ALS ID:</b> <code><?= e($shown[1]) ?></code> <b>Password:</b> <code><?= e($shown[2]) ?></code><small>This password shows only once. Write it down or print their QR pass now.</small></div><?php endif; ?>
<details class="card add" <?= !$teachers ? 'open' : '' ?>>
  <summary><?= icon('plus', 18) ?> Add a teacher or admin</summary>
  <form method="post" class="form grid2"><?= csrf_field() ?><input type="hidden" name="do" value="add">
    <label>First name<input name="first_name" required></label>
    <label>Middle name<input name="middle_name"></label>
    <label>Last name<input name="last_name" required></label>
    <label>Account type<select name="role"><option value="teacher">Teacher</option><option value="admin">Admin</option></select></label>
    <label data-teacher-only>District<?php $selD = 0; ?><select name="district_id"><option value="0">Choose district</option><?php foreach ($dists as $d): ?><option value="<?= (int)$d['id'] ?>"<?= isset($selD) && (int)$selD === (int)$d['id'] ? ' selected' : '' ?>><?= e($d['name'] . ' · ' . $d['municipality']) ?></option><?php endforeach; ?></select></label>
    <label>Email (optional)<input type="email" name="email"></label>
    <p class="muted small id-note"><?= icon('key', 14) ?> ALS ID and password are made for you.</p>
    <fieldset class="span2"><legend>CLCs they handle (teachers)</legend><div class="checks"><?php foreach ($clcs as $c): ?><label class="ck"><input type="checkbox" name="clcs[]" value="<?= (int)$c['id'] ?>"> <?= e($c['name']) ?></label><?php endforeach; ?><?= !$clcs ? '<span class="muted small">Add CLCs first on the CLCs page.</span>' : '' ?></div></fieldset>
    <button class="btn primary">Make account</button>
  </form>
</details>
<form class="filters" method="get"><input type="hidden" name="p" value="teachers">
  <select name="role"><option value="">Teachers and admins</option><option value="teacher" <?= $f['role'] === 'teacher' ? 'selected' : '' ?>>Teachers</option><option value="admin" <?= $f['role'] === 'admin' ? 'selected' : '' ?>>Admins</option></select>
  <?php require ROOT . '/app/views/_places.php'; ?><button class="btn">Show</button>
</form>
<div class="card">
<table class="tbl">
<thead><tr><th>Name</th><th>ALS ID · District · Username</th><th>CLCs</th><th>Last sign-in</th><th></th></tr></thead>
<tbody>
<?php $cn = array_column($clcs, 'name', 'id'); foreach ($teachers as $t): $ids = $t['clc_ids'] ? explode(',', $t['clc_ids']) : []; $self = (int)$t['id'] === (int)$me['id']; ?>
<tr class="<?= $t['active'] ? '' : 'off' ?>">
  <td><b><?= e(full_name($t)) ?></b> <span class="pill <?= $t['role'] === 'admin' ? 'pill-admin' : '' ?>"><?= e(ucfirst($t['role'])) ?></span><?= $t['active'] ? '' : ' <span class="pill">Turned off</span>' ?></td>
  <td><code><?= e($t['als_id']) ?></code>
    <?php if ($t['role'] === 'teacher'): ?>    <form method="post" class="dist-form"><?= csrf_field() ?><input type="hidden" name="do" value="district"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><?php $selD = $t['district_id']; ?><select name="district_id" aria-label="District" data-autosave><option value="0">Choose district</option><?php foreach ($dists as $d): ?><option value="<?= (int)$d['id'] ?>"<?= isset($selD) && (int)$selD === (int)$d['id'] ? ' selected' : '' ?>><?= e($d['name'] . ' · ' . $d['municipality']) ?></option><?php endforeach; ?></select></form><?php else: ?><span class="muted small">All districts</span><?php endif; ?><br><small class="muted"><?= $t['setup_done'] ? e($t['username']) : 'Not set up yet' ?></small></td>
  <td>
    <?php if ($t['role'] === 'teacher'): ?>
    <details class="inline"><summary><?= $ids ? e(implode(', ', array_map(fn($i) => $cn[$i] ?? '?', $ids))) : '<span class="muted">None</span>' ?></summary>
      <form method="post" class="checks"><?= csrf_field() ?><input type="hidden" name="do" value="clcs"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
        <?php foreach ($clcs as $c): ?><label class="ck"><input type="checkbox" name="clcs[]" value="<?= (int)$c['id'] ?>" <?= in_array((string)$c['id'], $ids, true) ? 'checked' : '' ?>> <?= e($c['name']) ?></label><?php endforeach; ?>
        <button class="btn sm">Save</button></form></details>
    <?php else: ?><span class="muted small">Whole division</span><?php endif; ?>
  </td>
  <td class="muted small"><?= $t['last_login'] ? e(date('M j, Y', strtotime($t['last_login']))) : 'Never' ?></td>
  <td class="acts-cell">
    <?php if ($self): ?><span class="muted small">This is you</span><?php else: ?>
    <form method="post" data-confirm="Make a new password for this account?"><?= csrf_field() ?><input type="hidden" name="do" value="reset"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="btn sm ghost">New password</button></form>
    <form method="post" data-confirm="Clear this PIN? They make a new one on their next sign-in."><?= csrf_field() ?><input type="hidden" name="do" value="pin"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="btn sm ghost">Reset PIN</button></form>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="toggle"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="btn sm ghost"><?= $t['active'] ? 'Turn off' : 'Turn on' ?></button></form>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
<?php if (!$teachers): ?><tr><td colspan="5" class="muted">No accounts here yet.</td></tr><?php endif; ?>
</tbody></table></div>
