<?php $st = STRANDS[$it['strand']]; ?>
<div class="page-head">
  <div><a class="back" href="<?= url('questions', ['grade' => $it['grade'], 's' => $it['strand'], 'l' => $it['level'], 'a' => $it['activity']]) ?>"><?= icon('back', 16) ?> Questions</a>
  <h1><?= e($st[0] . ' ' . $st[1]) ?> · Level <?= (int)$it['level'] ?> · Item <?= (int)$it['num'] ?></h1><p class="muted">Activity <?= (int)$it['activity'] ?> · <?= e(GRADES[$it['grade']]) ?></p></div>
  <div class="row-btns"><?php if ($prev): ?><a class="btn ghost" href="<?= url('question', ['id' => $prev]) ?>"><?= icon('back', 16) ?> Item before</a><?php endif; ?><?php if ($next): ?><a class="btn ghost" href="<?= url('question', ['id' => $next]) ?>">Next item <?= icon('arrow', 16) ?></a><?php endif; ?></div>
</div>
<form method="post" class="card form"><?= csrf_field() ?>
  <?php if ($it['image']): ?><div class="figs edit"><?php foreach (explode(',', $it['image']) as $p): ?><img src="assets/qimg/<?= e($p) ?>" alt="Picture from the module"><?php endforeach; ?></div>
  <label class="ck"><input type="checkbox" name="image_only" <?= $it['image_only'] ? 'checked' : '' ?>> Learners read the choices from the picture (use when the choices have fractions or symbols that do not show as text)</label><?php endif; ?>
  <label>Passage or reading text (shared by the items it belongs to; leave empty if none)<textarea name="passage" rows="5"><?= e($it['passage'] ?? '') ?></textarea></label>
  <label>Question<textarea name="question" rows="3" required><?= e($it['question']) ?></textarea></label>
  <div class="grid2">
    <?php foreach (['A' => 'opt_a', 'B' => 'opt_b', 'C' => 'opt_c', 'D' => 'opt_d'] as $k => $c): ?>
    <label class="opt-edit <?= $it['answer'] === $k ? 'is-key' : '' ?>"><span><input type="radio" name="answer" value="<?= $k ?>" <?= $it['answer'] === $k ? 'checked' : '' ?>> <?= $k ?> · correct answer</span><textarea name="<?= $c ?>" rows="2"><?= e($it[$c]) ?></textarea></label>
    <?php endforeach; ?>
  </div>
  <label>Explanation (shown after the learner answers)<textarea name="explanation" rows="3"><?= e($it['explanation']) ?></textarea></label>
  <label class="ck"><input type="checkbox" name="active" <?= $it['active'] ? 'checked' : '' ?>> Show this question to learners</label>
  <div class="row-btns"><button class="btn primary">Save question</button></div>
  <?php if ($it['updated_at']): ?><p class="muted small">Last changed <?= e(date('M j, Y g:i A', strtotime($it['updated_at']))) ?></p><?php endif; ?>
</form>
