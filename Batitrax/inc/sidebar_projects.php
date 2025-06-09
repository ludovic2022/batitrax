<?php if(!defined('BATITRAX')) exit; ?>
<ul>
  <?php
    $pStmt = $conn->prepare("SELECT id,name,emoji FROM projects WHERE account_id=?");
    $pStmt->execute([$user['account_id']]);
    $projects = $pStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($projects as $p):
  ?>
    <li>
      <a href="?view=projects&project_id=<?=$p['id']?>"
         class="<?= ($selectedProject==$p['id'])?'active':'' ?>">
        <?=htmlspecialchars($p['emoji'].' '.$p['name'])?>
      </a>
    </li>
  <?php endforeach; ?>
</ul>
