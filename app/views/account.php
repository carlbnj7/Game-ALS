<?php $u = me(); ?>
<div class="card narrow">
  <div class="acct"><?= avatar_html($u, 'big') ?><div><h2><?= e(full_name($u)) ?></h2><p class="muted"><?= e(ucfirst($u['role'])) ?> · <?= e($u['username']) ?></p></div></div>
  <details class="avatar-pick"<?= empty($u['avatar']) ? ' open' : '' ?>><summary><?= icon('user', 16) ?> Change profile picture</summary>
    <form method="post" class="av-grid"><?= csrf_field() ?><input type="hidden" name="do" value="preset">
      <?php for ($i = 1; $i <= AVATAR_PRESETS; $i++): ?><button name="preset" value="a<?= $i ?>" class="av-choice<?= ($u['avatar'] ?? '') === 'preset:a' . $i ? ' on' : '' ?>" aria-label="Avatar <?= $i ?>"><img src="assets/img/avatars/a<?= $i ?>.svg" alt="" width="56" height="56"></button><?php endfor; ?>
    </form>
    <form method="post" enctype="multipart/form-data" class="av-upload"><?= csrf_field() ?><input type="hidden" name="do" value="upload">
      <label class="btn sm"><?= icon('upload', 14) ?> Upload a photo<input type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-autosubmit-file hidden></label>
      <small class="muted">JPG, PNG or WEBP, up to 4 MB. It is cut to a square.</small>
    </form>
    <?php if (!empty($u['avatar'])): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="remove"><button class="linkish small">Remove picture</button></form><?php endif; ?>
  </details>
  <dl class="kv">
    <dt>ALS ID</dt><dd><code><?= e($u['als_id']) ?></code></dd>
    <dt>Username</dt><dd><?= e($u['username']) ?></dd>
    <?php if ($u['role'] === 'learner'): ?>
      <dt>Grade level</dt><dd><?= e(GRADES[$u['grade'] ?: 'ELEM']) ?></dd>
      <dt>CLC</dt><dd><?= e($clc['name'] ?? '—') ?></dd>
      <?php if ($u['lrn']): ?><dt>LRN</dt><dd><?= e($u['lrn']) ?></dd><?php endif; ?>
    <?php endif; ?>
    <dt>Last sign-in</dt><dd><?= $u['last_login'] ? e(date('M j, Y g:i A', strtotime($u['last_login']))) : '—' ?></dd>
  </dl>
  <button type="button" class="btn install-btn" data-install hidden><?= icon('download', 16) ?> Install Game ALS on this device</button>
  <p class="install-ios" data-install-ios hidden>On iPhone or iPad: tap <b>Share</b> at the bottom of Safari, then <b>Add to Home Screen</b>.</p>
  <div class="row-btns">
    <a class="btn" href="<?= url('password') ?>"><?= icon('key', 16) ?> Change password</a>
    <a class="btn" href="<?= url('pin') ?>"><?= icon('pin', 16) ?> Username and PIN</a>
    <button type="button" class="btn ghost" data-theme-toggle><?= icon('moon', 16) ?> Light / dark</button>
    <form method="post" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="btn ghost"><?= icon('logout', 16) ?> Sign out</button></form>
  </div>
</div>
