<?php if(!defined('BATITRAX')) exit; ?>
<aside class="menu sidebar">
  <ul>
    <?php if($user['role']==='superadmin'): ?>
      <li><a href="?view=accounts" class="menu-item <?php echo ($view==='accounts')?'active':'' ?>" title="Comptes"><i class="fas fa-building"></i><span class="menu-text">Comptes</span></a></li>
      <li><a href="?view=users" class="menu-item <?php echo ($view==='users')?'active':'' ?>" title="Utilisateurs"><i class="fas fa-users"></i><span class="menu-text">Utilisateurs</span></a></li>
      <li><a href="?view=invoices" class="menu-item <?php echo ($view==='invoices')?'active':'' ?>" title="Factures"><i class="fas fa-file-invoice"></i><span class="menu-text">Factures</span></a></li>
    <?php else: ?>
      <li><a href="?view=projects" class="menu-item <?php echo ($view==='projects')?'active':'' ?>" title="Projets"><i class="fas fa-tasks"></i><span class="menu-text">Projets</span></a></li>
      <li><a href="?view=users" class="menu-item <?php echo ($view==='users')?'active':'' ?>" title="Utilisateurs"><i class="fas fa-users"></i><span class="menu-text">Utilisateurs</span></a></li>
      <li><a href="?view=invoices" class="menu-item <?php echo ($view==='invoices')?'active':'' ?>" title="Factures"><i class="fas fa-file-invoice"></i><span class="menu-text">Factures</span></a></li>
      <?php if($user['role']==='admin'): ?>
        <li><a href="?view=chat" class="menu-item <?php echo ($view==='chat')?'active':'' ?>" title="Chat"><i class="fas fa-comments"></i><span class="menu-text">Chat</span></a></li>
        <li><a href="?view=personnalisation" class="menu-item <?php echo ($view==='personnalisation')?'active':'' ?>" title="Personnalisation"><i class="fas fa-paint-brush"></i><span class="menu-text">Personnalisation</span></a></li>
      <?php endif; ?>
    <?php endif; ?>
  </ul>
</aside>
