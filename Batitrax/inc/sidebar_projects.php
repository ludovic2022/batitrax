<?php if(!defined('BATITRAX')) exit; ?>
<ul class="projects-list">
  <?php
    $pStmt = $conn->prepare("SELECT id,name,emoji,address FROM projects WHERE account_id=?");
    $pStmt->execute([$user['account_id']]);
    $projects = $pStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($projects as $p):
  ?>
    <li>
  <a href="?view=projects&project_id=<?=$p['id']?>" class="<?= ($selectedProject==$p['id'])?'active':'' ?>">
    <div class="project-header">
      <span class="project-emoji"><?=htmlspecialchars($p['emoji'])?></span>
      <span class="project-name"><?=htmlspecialchars($p['name'])?></span>
    </div>
    <?php if(!empty($p['address'])): ?>
    <div class="project-meta">
      <span class="meta-label">texte</span>
      <span class="meta-value"><?= htmlspecialchars(substr($p['address'], 0, 20)) ?><?= strlen($p['address']) > 20 ? '…' : '' ?></span>
    </div>
    <?php endif; ?>
  </a>
</li>
  <?php endforeach; ?>
</ul>
<div class="create-project-btn">
  <a href="?view=create_project" id="sideCreateBtn" class="save-btn">Créer un projet</a>
</div>

