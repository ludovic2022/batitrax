<?php if(!defined('BATITRAX')) exit; ?>

<section id="view-projects-settings" class="view <?= $view==='settings'?'active':'' ?>">
  <div class="card">
    <h3>Projets</h3>
    <table id="projects-settings-table">
      <thead>
        <tr>
          <th>Emoji</th>
          <th>Nom</th>
          <th>Adresse</th>
          <th>Resp.</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // récupère tous les projets du compte
        $pStmt = $conn->prepare("
          SELECT p.id,p.name,p.emoji,p.address,
                 CONCAT(u.first_name,' ',u.last_name) AS manager
          FROM projects p
          LEFT JOIN users u ON p.manager_id=u.id
          WHERE p.account_id=?
        ");
        $pStmt->execute([$user['account_id']]);
        foreach($pStmt->fetchAll(PDO::FETCH_ASSOC) as $p): ?>
        <tr data-project-id="<?=$p['id']?>">
          <td class="emoji"><?=htmlspecialchars($p['emoji'])?></td>
          <td class="name"><?=htmlspecialchars($p['name'])?></td>
          <td class="address"><?=htmlspecialchars($p['address'])?></td>
          <td class="manager"><?=htmlspecialchars($p['manager'])?></td>
          <td class="actions">
            <button class="edit-btn">✏️</button>
            <button class="delete-btn">🗑️</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<script>
document.querySelectorAll('#projects-settings-table tbody tr').forEach(row=>{
  const id = row.dataset.projectId;

  // Edition inline
  row.querySelector('.edit-btn').onclick = ()=>{
    if(row.classList.contains('editing')) return;
    row.classList.add('editing');

    ['emoji','name','address','manager'].forEach(field=>{
      const cell = row.querySelector('.'+field);
      const val  = cell.innerText.trim();
      if(field==='manager'){
        // Crée un select pour manager
        const sel = document.createElement('select');
        sel.className = 'edit-'+field;
        fetch('../api/account.php?action=list_users&account_id=<?=$user['account_id']?>')
          .then(r=>r.json())
          .then(users=>{
            users.forEach(u=>{
              const o = document.createElement('option');
              o.value = u.id; o.textContent = u.fullname;
              if(u.fullname === val) o.selected = true;
              sel.appendChild(o);
            });
          });
        cell.innerHTML = '';
        cell.appendChild(sel);
      } else {
        const inp = document.createElement('input');
        inp.className = 'edit-'+field;
        inp.value = val;
        if(field==='emoji') inp.style.width = '3rem';
        cell.innerHTML = '';
        cell.appendChild(inp);
      }
    });

    row.querySelector('.actions').innerHTML = `
      <button class="save-btn">💾</button>
      <button class="cancel-btn">✖️</button>
    `;

    // Cancel
    row.querySelector('.cancel-btn').onclick = ()=>{
      window.location.reload();
    };

    // Save
    row.querySelector('.save-btn').onclick = ()=>{
      const data = new URLSearchParams();
      data.append('project_id', id);
      ['emoji','name','address'].forEach(f=>{
        data.append(f, row.querySelector('.edit-'+f).value);
      });
      data.append('manager_id', row.querySelector('select.edit-manager').value);

      fetch('../api/project.php?action=update_project', {
        method:'POST', body: data
      }).then(r=>r.json()).then(res=>{
        if(res.success){
          window.location.reload();
        } else {
          alert('Erreur: '+res.message);
        }
      });
    };
  };

  // Delete
  row.querySelector('.delete-btn').onclick = ()=>{
    if(!confirm('Supprimer ce projet ?')) return;
    const data = new URLSearchParams();
    data.append('project_id', id);
    fetch('../api/project.php?action=delete_project', {
      method:'POST', body: data
    }).then(r=>r.json()).then(res=>{
      if(res.success) row.remove();
      else alert('Erreur: '+res.message);
    });
  };
});
</script>
