<div class="page-head"><div><h1>Municipalities and districts</h1><p class="muted">Add each municipality, then its districts. CLCs pick their district from this list, and the leaderboard, learners, passes and reports can be filtered by them.</p></div></div>
<form method="post" class="card form inline-form"><?= csrf_field() ?><input type="hidden" name="do" value="add_mun">
  <label>New municipality or city<input name="name" required maxlength="100" placeholder="e.g. Malaybalay City"></label><button class="btn primary"><?= icon('plus', 16) ?> Add municipality</button>
</form>
<?php if (!$muns): ?><div class="card empty"><p>No municipalities yet. Add the first one above.</p></div><?php endif; ?>
<div class="mun-grid">
<?php foreach ($muns as $m): $ds = $dists[$m['id']] ?? []; ?>
  <section class="card mun">
    <header class="mun-h">
      <span class="mun-ic"><?= icon('city', 22) ?></span>
      <form method="post" class="ren"><?= csrf_field() ?><input type="hidden" name="do" value="ren_mun"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><input name="name" value="<?= e($m['name']) ?>" required maxlength="100" aria-label="Municipality name"><button class="btn sm ghost">Rename</button></form>
      <?php if (!$ds): ?><form method="post" data-confirm="Remove this municipality?"><?= csrf_field() ?><input type="hidden" name="do" value="del_mun"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn sm ghost" aria-label="Remove municipality"><?= icon('x', 14) ?></button></form><?php endif; ?>
    </header>
    <p class="muted small"><?= count($ds) ?> district<?= count($ds) === 1 ? '' : 's' ?> · <?= (int)$m['clcs'] ?> CLC<?= $m['clcs'] == 1 ? '' : 's' ?> · <?= (int)$m['learners'] ?> learner<?= $m['learners'] == 1 ? '' : 's' ?></p>
    <ul class="dist-list">
      <?php foreach ($ds as $d): ?>
      <li>
        <form method="post" class="ren"><?= csrf_field() ?><input type="hidden" name="do" value="ren_dist"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>"><input name="name" value="<?= e($d['name']) ?>" required maxlength="100" aria-label="District name"><button class="btn sm ghost">Save</button></form>
        <span class="small muted" title="<?= (int)$d['learners'] ?> learners"><?= (int)$d['clcs'] ?> CLC<?= $d['clcs'] == 1 ? '' : 's' ?></span>
        <?php if (!$d['clcs']): ?><form method="post" data-confirm="Remove this district?"><?= csrf_field() ?><input type="hidden" name="do" value="del_dist"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>"><button class="btn sm ghost" aria-label="Remove district"><?= icon('x', 14) ?></button></form><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
    <form method="post" class="add-dist"><?= csrf_field() ?><input type="hidden" name="do" value="add_dist"><input type="hidden" name="mun" value="<?= (int)$m['id'] ?>"><input name="name" required maxlength="100" placeholder="New district, e.g. District III" aria-label="New district"><button class="btn sm"><?= icon('plus', 14) ?> Add</button></form>
  </section>
<?php endforeach; ?>
</div>
