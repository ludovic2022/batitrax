<?php if(!defined('BATITRAX')) exit; ?>
<nav class="menu">
  <ul>
    <?php if($user['role']==='superadmin'): ?>
      <li><a href="?view=accounts" class="<?= $view==='accounts'?'active':'' ?>">Comptes</a></li>
      <li><a href="?view=users" class="<?= $view==='users'?'active':'' ?>">Utilisateurs</a></li>
      <li><a href="?view=invoices" class="<?= $view==='invoices'?'active':'' ?>">Factures</a></li>
    <?php else: ?>
      <li><a href="?view=projects" class="<?= $view==='projects'?'active':'' ?>">Projets</a></li>
      <li><a href="?view=users" class="<?= $view==='users'?'active':'' ?>">Utilisateurs</a></li>
      <li><a href="?view=invoices" class="<?= $view==='invoices'?'active':'' ?>">Factures</a></li>
      <?php if($user['role']==='admin'): ?>
        <li><a href="?view=chat" class="<?= $view==='chat'?'active':'' ?>">Chat</a></li>
      <?php endif; ?>
    <?php endif; ?>
  </ul>
</nav>
