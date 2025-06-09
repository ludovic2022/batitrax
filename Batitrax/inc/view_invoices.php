<?php if(!defined('BATITRAX')) exit; ?>
<section id="view-invoices" class="view <?= $view==='invoices'?'active':'' ?>">
  <div class="card">
    <h3>Factures</h3>
    <?php if ($user['role'] === 'superadmin'): ?>
      <a href="https://dashboard.stripe.com/login" target="_blank" class="button">Aller sur la plateforme Stripe</a>
    <?php else: ?>
      <a href="https://dashboard.stripe.com/login" target="_blank" class="button">Aller sur votre site de facturation</a>
    <?php endif; ?>
  </div>
</section>
