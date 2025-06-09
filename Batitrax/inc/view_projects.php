<?php if(!defined('BATITRAX')) exit; ?>
<!-- Styles -->
<style>
  #projects-list { list-style:none; padding:0; margin:0; }
  .project-item { margin-bottom:1rem; }
  .project-header { display:flex; align-items:center; gap:0.5rem; cursor:pointer; }
  .project-details { display:none; margin-top:0.5rem; }
  .emoji-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:0.5rem; margin:0.5rem 0; }
  .emoji-item { cursor:pointer; padding:0.5rem; border:1px solid #ccc; border-radius:4px; text-align:center; font-size:1.5rem; }
  .emoji-item.selected { border-color:var(--primary); background:var(--primary); color:#fff; }
  .map-thumb { width:150px; height:100px; border:1px solid #ccc; cursor:pointer; margin-top:0.5rem; }
  .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:2000; }
  .modal-content { background:#fff; border-radius:4px; padding:1rem; position:relative; max-width:90%; max-height:90%; overflow:auto; }
  .modal-close { position:absolute; top:0.5rem; right:0.75rem; font-size:1.5rem; cursor:pointer; }
  #toast { position:fixed; top:1rem; right:1rem; padding:1rem; border-radius:4px; background:var(--secondary); color:#fff; display:none; z-index:3000; }
  #create-project-btn { background: var(--primary); color:#fff; border:none; padding:0.5rem 1rem; border-radius:4px; cursor:pointer; margin-bottom:1rem; }
  #create-form input, #create-form select, #create-form textarea { width:100%; margin-bottom:0.5rem; padding:0.5rem; border:1px solid #ccc; border-radius:4px; }
  #create-map { width:100%; height:200px; margin-bottom:0.5rem; }
  #create-form button { background:var(--secondary); color:#fff; border:none; padding:0.5rem 1rem; cursor:pointer; border-radius:4px; }
</style>

<section id="view-projects" class="view <?= $view==='projects'?'active':'' ?>">
  <div class="card">
    <h3 style="display:flex; justify-content:space-between; align-items:center;">
      <span>Projets</span>
      <button id="create-project-btn">Créer un projet</button>
    </h3>

    <?php
      // Récupération des projets
      $pStmt = $conn->prepare("SELECT id,name,emoji,address,lat,lng,manager_id FROM projects WHERE account_id=?");
      $pStmt->execute([$user['account_id']]);
      $projects = $pStmt->fetchAll(PDO::FETCH_ASSOC);
      // Récupération des utilisateurs
      $uStmt = $conn->prepare("SELECT id, CONCAT(first_name,' ',last_name) AS fullname FROM users WHERE account_id=?");
      $uStmt->execute([$user['account_id']]);
      $users = $uStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div id="toast"></div>
    <ul id="projects-list">
      <?php foreach($projects as $p): ?>
      <li class="project-item" data-project='<?= json_encode($p, JSON_HEX_TAG) ?>'>
        <div class="project-header">
          <span class="emoji"><?=htmlspecialchars($p['emoji'])?></span>
          <strong class="name"><?=htmlspecialchars($p['name'])?></strong>
          <button class="edit-btn">✏️</button>
          <button class="delete-btn">🗑️</button>
        </div>
        <div class="project-details">
          <?php if($p['address']): ?>
            <div><em>Adresse :</em> <span class="addr"><?=htmlspecialchars($p['address'])?></span></div>
            <div class="map-thumb"
                 data-lat="<?=$p['lat']?>"
                 data-lng="<?=$p['lng']?>">
            </div>
          <?php endif; ?>
          <?php if($p['manager_id']): 
            $m = $conn->prepare("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id=?");
            $m->execute([$p['manager_id']]);
            $mgr = $m->fetchColumn();
          ?>
            <div><em>Resp. :</em> <span class="mgr"><?=htmlspecialchars($mgr)?></span></div>
          <?php endif; ?>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- modals de création et de carte ici (inchangés) -->

<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const toast = document.getElementById('toast');
  const usersList = <?= json_encode($users, JSON_HEX_TAG) ?>;

  function showToast(msg){
    toast.innerText = msg;
    toast.style.display = 'block';
    setTimeout(()=> toast.style.display='none', 3000);
  }

  document.querySelectorAll('.project-item').forEach(li=>{
    const p = JSON.parse(li.dataset.project);
    const hdr = li.querySelector('.project-header');
    const det = li.querySelector('.project-details');

    // Toggle détails
    hdr.addEventListener('click', e=>{
      if(e.target.closest('button')) return;
      det.style.display = det.style.display==='none'? 'block' : 'none';
    });

    // Inline edit
    hdr.querySelector('.edit-btn').addEventListener('click', ()=>{
      if(det.style.display==='none') hdr.click();
      if(li.querySelector('.project-form')) return;

      // Crée le formulaire
      const form = document.createElement('div');
      form.className = 'project-form';
      form.style.marginTop = '0.5rem';
      // Génère la liste d'émojis inline
      const emojis = ['🏗️','🛠️','🚧','🏠','🔨','📐','📦','📝','🚀','💼','🎯','✅','📊','🗂️','📁','⚙️'];
      let emojiGrid = '<div class="emoji-grid">';
      emojis.forEach(e => {
        emojiGrid += `<span class="emoji-item" data-emoji="${e}">${e}</span>`;
      });
      emojiGrid += '</div>';

      // Génère select users
      let opts = '';
      usersList.forEach(u=>{
        opts += `<option value="${u.id}"${u.id==p.manager_id?' selected':''}>${u.fullname}</option>`;
      });

      form.innerHTML = `
        <button type="button" class="inline-emoji-btn">📋</button>
        ${emojiGrid}
        <input type="hidden" name="emoji" value="${p.emoji}">
        <input type="text" name="name" value="${p.name}">
        <input type="text" name="address" value="${p.address}">
        <select name="manager_id">${opts}</select>
        <button class="save-btn">💾</button>
        <button class="cancel-btn">✖️</button>
      `;
      det.appendChild(form);

      // Émoji inline
      const picker = form.querySelector('.emoji-grid');
      form.querySelector('.inline-emoji-btn').onclick = ()=>{
        picker.style.display = picker.style.display==='none'?'grid':'none';
      };
      picker.querySelectorAll('.emoji-item').forEach(item=>{
        item.onclick = ()=>{
          picker.querySelectorAll('.emoji-item').forEach(i=>i.classList.remove('selected'));
          item.classList.add('selected');
          form.querySelector('input[name="emoji"]').value = item.dataset.emoji;
        };
      });

      // Cancel
      form.querySelector('.cancel-btn').onclick = ()=> form.remove();

      // Save
      form.querySelector('.save-btn').onclick = ()=>{
        const data = new URLSearchParams();
        data.append('project_id', p.id);
        ['emoji','name','address','manager_id'].forEach(f=>{
          data.append(f, form.querySelector(`[name="${f}"]`).value);
        });
        fetch('../api/project.php?action=update_project',{
          method:'POST', body: data
        }).then(r=>r.json()).then(res=>{
          if(res.success){
            hdr.querySelector('.emoji').innerText = data.get('emoji');
            hdr.querySelector('.name').innerText  = data.get('name');
            showToast('Modification sauvegardée');
            form.remove();
          } else {
            showToast('Erreur : '+res.message);
          }
        });
      };
    });

    // Delete
    hdr.querySelector('.delete-btn').onclick = ()=>{
      if(!confirm('Supprimer ce projet ?')) return;
      const data = new URLSearchParams();
      data.append('project_id', p.id);
      fetch('../api/project.php?action=delete_project',{
        method:'POST', body: data
      }).then(r=>r.json()).then(res=>{
        if(res.success) li.remove();
        else showToast('Erreur : '+res.message);
      });
    };

    // Mini-carte & popup carte (inchangé)
    const thumb = li.querySelector('.map-thumb');
    if(thumb){
      let inited=false;
      thumb.addEventListener('mouseenter', ()=>{
        if(inited) return;
        const lat=+thumb.dataset.lat, lng=+thumb.dataset.lng;
        const m = L.map(thumb).setView([lat,lng],13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(m);
        L.marker([lat,lng]).addTo(m);
        inited=true;
      });
      thumb.onclick = ()=>{
        const modal = document.getElementById('mapModal');
        modal.style.display='flex';
        setTimeout(()=>{
          const lat=+thumb.dataset.lat, lng=+thumb.dataset.lng;
          const div=document.getElementById('modalMap'); div.innerHTML='';
          const bigMap = L.map(div).setView([lat,lng],13);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(bigMap);
          L.marker([lat,lng]).addTo(bigMap);
          bigMap.invalidateSize();
        },50);
      };
    }
  });
});
</script>
