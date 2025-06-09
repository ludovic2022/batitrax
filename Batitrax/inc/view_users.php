<?php if(!defined('BATITRAX')) exit; ?>
<section id="view-users" class="view <?= $view==='users'?'active':'' ?>">
  <div class="card">
    <h3>Utilisateurs</h3>
    <?php
      // Superadmin: choose account
      if ($user['role'] === 'superadmin'): ?>
        <form method="get" action="" class="inline" style="margin-bottom:1rem;">
          <input type="hidden" name="view" value="users">
          <label for="view_account_usr">Compte :</label>
          <select name="view_account" id="view_account_usr" required>
            <option value="">-- Choisissez --</option>
            <?php 
              $accounts = $conn->query("SELECT id,name FROM accounts")->fetchAll(PDO::FETCH_ASSOC);
              foreach ($accounts as $a): ?>
                <option value="<?= $a['id'] ?>" <?= ($viewAccount == $a['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['name']) ?>
                </option>
            <?php endforeach; ?>
          </select>
          <button>Afficher</button>
        </form>
        <?php 
        if (!$viewAccount) {
          echo '<p>Sélectionnez un compte pour voir/éditer ses utilisateurs.</p>';
          return;
        }
      endif;

      // Determine account context
      $accId = ($user['role'] === 'superadmin' ? $viewAccount : $user['account_id']);
      $uStmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, role FROM users WHERE account_id = ?");
      $uStmt->execute([$accId]);
      $usrs = $uStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Toast notification -->
    <div id="toast" style="
      position:fixed; top:1rem; right:1rem;
      padding:1rem; border-radius:4px;
      background: var(--secondary); color:#fff;
      display:none; z-index:1000;
    "></div>

    <table id="users-table">
      <thead>
        <tr>
          <th>Prénom</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Rôle</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($usrs as $u): ?>
        <tr data-user-id="<?= $u['id'] ?>">
          <td class="first_name"><?= htmlspecialchars($u['first_name']) ?></td>
          <td class="last_name"><?= htmlspecialchars($u['last_name']) ?></td>
          <td class="email"><?= htmlspecialchars($u['email']) ?></td>
          <td class="phone"><?= htmlspecialchars($u['phone']) ?></td>
          <td class="role"><?= htmlspecialchars($u['role']) ?></td>
          <td class="actions">
            <button class="edit-btn">✏️</button>
            <button class="delete-btn">🗑️</button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <h4>Ajouter un utilisateur</h4>
    <form method="post" action="../api/account.php?action=create_user" class="inline">
      <input name="first_name" placeholder="Prénom" required>
      <input name="last_name"  placeholder="Nom"     required>
      <input name="phone"      placeholder="Téléphone" required>
      <input name="email"      placeholder="Email"    required>
      <select name="role">
        <?php if ($user['role'] === 'superadmin'): ?><option value="admin">Admin</option><?php endif; ?>
        <option value="salarie">Salarié</option>
        <option value="externe">Externe</option>
        <option value="encadrant">Encadrant</option>
      </select>
      <input type="hidden" name="account_id" value="<?= $accId ?>">
      <button>Ajouter</button>
    </form>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const table = document.getElementById('users-table');
  const toast = document.getElementById('toast');
  const roles = <?= json_encode($user['role'] === 'superadmin' ? ['admin','salarie','externe','encadrant'] : ['salarie','externe','encadrant']) ?>;

  function showToast(message) {
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3000);
  }

  table.addEventListener('click', function(e) {
    const row = e.target.closest('tr[data-user-id]');
    if (!row) return;
    const uid = row.dataset.userId;

    // Edit
    if (e.target.matches('.edit-btn')) {
      if (row.classList.contains('editing')) return;
      row.classList.add('editing');
      ['first_name','last_name','email','phone'].forEach(field => {
        const cell = row.querySelector('.' + field);
        const val = cell.textContent.trim();
        cell.innerHTML = `<input type="text" name="${field}" value="${val}" />`;
      });
      const roleCell = row.querySelector('.role');
      const current = roleCell.textContent.trim();
      const select = document.createElement('select');
      select.name = 'role';
      roles.forEach(r => {
        const opt = document.createElement('option');
        opt.value = r; opt.textContent = r;
        if (r === current) opt.selected = true;
        select.appendChild(opt);
      });
      roleCell.textContent = '';
      roleCell.appendChild(select);
      row.querySelector('.actions').innerHTML = `
        <button class="save-btn">💾</button>
        <button class="cancel-btn">✖️</button>
      `;
    }

    // Save
    if (e.target.matches('.save-btn')) {
      const inputs = row.querySelectorAll('input, select');
      const data = new URLSearchParams();
      data.append('user_id', uid);
      inputs.forEach(inp => {
        data.append(inp.name, inp.value);
      });
      fetch('../api/account.php?action=update_user', {
        method: 'POST', body: data
      })
      .then(response => response.json())
      .then(res => {
        if (res.success) {
          // Update cells
          ['first_name','last_name','email','phone','role'].forEach(field => {
            const cell = row.querySelector('.' + field);
            cell.textContent = data.get(field);
          });
          row.querySelector('.actions').innerHTML = `
            <button class="edit-btn">✏️</button>
            <button class="delete-btn">🗑️</button>
          `;
          row.classList.remove('editing');
          showToast('Modification sauvegardée');
        } else {
          showToast('Erreur: ' + res.message);
        }
      });
    }

    // Cancel
    if (e.target.matches('.cancel-btn')) {
      window.location.reload();
    }

    // Delete
    if (e.target.matches('.delete-btn')) {
      if (!confirm('Supprimer cet utilisateur ?')) return;
      const data = new URLSearchParams();
      data.append('user_id', uid);
      fetch('../api/account.php?action=delete_user', {
        method: 'POST', body: data
      })
      .then(() => row.remove());
    }
  });
});
</script>
