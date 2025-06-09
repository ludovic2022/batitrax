<?php if(!defined('BATITRAX')) exit; ?>
<?php $isAdmin = isset($user['role']) && $user['role']==='admin'; ?>
<!-- Styles -->
<style>
  #projects-list { list-style:none; padding:0; margin:0; }
  .project-item { margin-bottom:1rem; }
  .project-header { display:flex; align-items:center; gap:0.5rem; cursor:pointer; }
  .project-details { display:none; margin-top:0.5rem; }
  .map-thumb { width:150px; height:100px; border:1px solid #ccc; cursor:pointer; margin-top:0.5rem; }

  /* --- Modal générique --- */
  .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:2000; }
  .modal-content { background:#fff; border-radius:4px; padding:1rem; position:relative; max-width:90%; max-height:90%; overflow:auto; }
  .modal-close { position:absolute; top:0.5rem; right:0.75rem; font-size:1.5rem; cursor:pointer; }

  /* --- Toast --- */
  #toast { position:fixed; top:1rem; right:1rem; padding:1rem; border-radius:4px; background:var(--secondary); color:#fff; display:none; z-index:3000; }

  /* --- Bouton créer --- */
  #create-project-btn { background: var(--primary); color:#fff; border:none; padding:0.5rem 1rem; border-radius:4px; cursor:pointer; margin-bottom:1rem; }

  /* --- Formulaires --- */
  input, select { width:100%; margin-bottom:0.5rem; padding:0.5rem; border:1px solid #ccc; border-radius:4px; }
  #create-map { width:100%; height:200px; margin-bottom:0.5rem; border:1px solid #ccc; }
  .save-btn { background:var(--secondary); color:#fff; border:none; padding:0.5rem 1rem; cursor:pointer; border-radius:4px; }
  .cancel-btn { background:#999; color:#fff; border:none; padding:0.5rem 1rem; cursor:pointer; border-radius:4px; }

  /* --- Emoji picker --- */
  .emoji-picker { position:relative; margin-bottom:0.5rem; }
  .emoji-selected { font-size:2rem; cursor:pointer; display:inline-block; padding:0.25rem 0.5rem; border:1px solid #ccc; border-radius:4px; }
  .emoji-grid-picker { display:none; position:absolute; top:110%; left:0; background:#fff; border:1px solid #ccc; border-radius:4px; padding:0.5rem; z-index:2100;
                       grid-template-columns:repeat(8,1.75rem); gap:0.25rem; max-height:15rem; overflow:auto; }
  .emoji-grid-picker .emoji-item { font-size:1.5rem; cursor:pointer; width:1.75rem; height:1.75rem; display:flex; align-items:center; justify-content:center; border-radius:4px; }
  .emoji-grid-picker .emoji-item:hover { background:var(--primary); color:#fff; }

  /* --- Project users list --- */
  .proj-users { margin-top:0.5rem; }
  .proj-users ul { list-style:none; padding:0; margin:0; }
  .proj-users li { display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem; }
  .proj-users .remove-user-btn { cursor:pointer; border:none; background:none; }
  .add-user-btn { background:var(--primary); color:#fff; border:none; padding:0.25rem 0.5rem; border-radius:4px; cursor:pointer; font-size:0.8rem; }

</style>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<section id="view-projects" class="view <?= $view==='projects'?'active':'' ?>">
  <div class="card">
    <h3 style="display:flex; justify-content:space-between; align-items:center;">
      <span>Projets</span>
      <button id="create-project-btn">Créer un projet</button>
    </h3>

    <?php
      $pStmt = $conn->prepare("SELECT id,name,emoji,address,lat,lng,manager_id FROM projects WHERE account_id=?");
      $pStmt->execute([$user['account_id']]);
      $projects = $pStmt->fetchAll(PDO::FETCH_ASSOC);

      $uStmt = $conn->prepare("SELECT id, CONCAT(first_name,' ',last_name) AS fullname FROM users WHERE account_id=?");
      $uStmt->execute([$user['account_id']]);
      $users = $uStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div id="toast"></div>
    <ul id="projects-list">
      <?php foreach($projects as $p): ?>
      <li class="project-item" data-project='<?= json_encode($p, JSON_HEX_TAG) ?>'>
        <div class="project-header">
          <span class="emoji"><?= htmlspecialchars($p['emoji']) ?></span>
          <strong class="name"><?= htmlspecialchars($p['name']) ?></strong>
          <button class="edit-btn">✏️</button>
          <button class="delete-btn">🗑️</button>
        </div>
        <div class="project-details">
          <?php if($p['address']): ?>
            <div><em>Adresse :</em> <span class="addr"><?= htmlspecialchars($p['address']) ?></span></div>
            <div class="map-thumb" data-lat="<?= $p['lat'] ?>" data-lng="<?= $p['lng'] ?>"></div>
          <?php endif; ?>
          <?php if($p['manager_id']): 
            $m = $conn->prepare("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id=?");
            $m->execute([$p['manager_id']]);
            $mgr = $m->fetchColumn();
          ?>
            <div><em>Resp. :</em> <span class="mgr"><?= htmlspecialchars($mgr) ?></span></div>
          <?php endif; ?>

          <!-- Utilisateurs autorisés -->
          <div class="proj-users" data-project-id="<?= $p['id'] ?>" data-loaded="0">
            <?php if($isAdmin): ?>
              <button class="add-user-btn">+ Utilisateur</button>
            <?php endif; ?>
            <ul class="users-list"></ul>
          </div>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- Modal: Ajouter un utilisateur -->
<div id="addUserModal" class="modal">
  <div class="modal-content">
    <span class="modal-close">&times;</span>
    <h3>Ajouter un utilisateur au projet</h3>
    <form id="add-user-form">
      <input type="hidden" name="project_id">
      <input type="hidden" name="role" value="viewer">
      <select name="user_id" required>
        <option value="">-- Utilisateur --</option>
        <?php foreach($users as $u): ?>
          <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['fullname']) ?></option>
        <?php endforeach; ?>
      </select>
      <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
        <button type="submit" class="save-btn">Ajouter</button>
        <button type="button" class="cancel-btn">Annuler</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Création de projet -->
<div id="createProjectModal" class="modal">
  <div class="modal-content">
    <span class="modal-close">&times;</span>
    <h3>Nouveau projet</h3>
    <form id="create-form" method="POST" action="../api/project.php?action=create_project">
      <!-- Emoji picker -->
      <div class="emoji-picker">
        <span id="selectedEmoji" class="emoji-selected">🏗️</span>
        <div id="emojiGrid" class="emoji-grid-picker">
          <?php
            $emojis = ['🏗️','🛠️','🚧','🏠','🔨','📐','📦','📝','🚀','💼',
                       '🎯','✅','📊','🗂️','📁','⚙️','🧱','🏢','🏘️','🏚️',
                       '🏛️','🏨','🏩','🏣','🏤','⛪','🕌','🛕','🕍','🏯',
                       '🏟️','🎡','🎢','🎪','🏖️','🏜️','🏝️','🏞️','🌆','🌇'];
            foreach($emojis as $e){
              echo '<span class="emoji-item" data-emoji="'.$e.'">'.$e.'</span>';
            }
          ?>
        </div>
        <input type="hidden" name="emoji" value="🏗️" required>
      </div>

      <input type="text" name="name" placeholder="Nom du projet" required>
      <input type="text" name="address" placeholder="Adresse" required>

      <div id="create-map"></div>

      <select name="manager_id" required>
        <option value="">-- Responsable --</option>
        <?php foreach($users as $u): ?>
          <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['fullname']) ?></option>
        <?php endforeach; ?>
      </select>

      <input type="hidden" name="lat" value="0">
      <input type="hidden" name="lng" value="0">

      <div style="margin-top:0.5rem; display:flex; gap:0.5rem;">
        <button type="submit" class="save-btn">Créer</button>
        <button type="button" class="cancel-btn">Annuler</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const toast = document.getElementById('toast');
  const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;

  const usersList = <?= json_encode($users, JSON_HEX_TAG) ?>;

  function showToast(msg){
    toast.textContent = msg;
    toast.style.display = 'block';
    setTimeout(()=> toast.style.display='none', 3000);
  }

  function api(url, opts={}){ return fetch(url, opts).then(r=>r.json()); }

  /* --------- Gestion utilisateurs par projet --------- */
  document.querySelectorAll('.project-item').forEach(li=>{
    const p   = JSON.parse(li.dataset.project);
    const hdr = li.querySelector('.project-header');
    const det = li.querySelector('.project-details');
    const usersDiv = det.querySelector('.proj-users');
    const usersUl  = usersDiv.querySelector('.users-list');
    const addUserBtn = usersDiv.querySelector('.add-user-btn');

    hdr.addEventListener('click', e=>{
      if(e.target.closest('button')) return;
      const show = det.style.display==='none' || !det.style.display;
      det.style.display = show ? 'block' : 'none';
      if(show && usersDiv.dataset.loaded==='0') loadUsers();
    });

    function renderUsers(arr){
      usersUl.innerHTML='';
      arr.forEach(u=>{
        const li = document.createElement('li');
        li.innerHTML = '<span>'+u.fullname+'</span>';
        if(isAdmin){
          const btn = document.createElement('button');
          btn.textContent='❌';
          btn.className='remove-user-btn';
          btn.onclick = ()=> removeUser(u.id);
          li.appendChild(btn);
        }
        usersUl.appendChild(li);
      });
    }

    function loadUsers(){
      api('../api/project_users.php?action=list_users&project_id='+p.id)
        .then(res=>{
          if(res.success){
            renderUsers(res.users);
            usersDiv.dataset.loaded='1';
          }
        });
    }

    function removeUser(uid){
      if(!confirm('Retirer cet utilisateur ?')) return;
      const data = new URLSearchParams({action:'delete_user', project_id:p.id, user_id:uid});
      api('../api/project_users.php', {method:'POST', body:data})
        .then(res=>{
          if(res.success){ showToast('Retiré'); usersDiv.dataset.loaded='0'; loadUsers(); }
        });
    }

    /* -- Add user modal */
    if(addUserBtn){
      const addModal = document.getElementById('addUserModal');
      const addForm  = document.getElementById('add-user-form');
      const closeAdd = addModal.querySelector('.modal-close');
      const cancelAdd= addModal.querySelector('.cancel-btn');

      const hideAdd = () => addModal.style.display='none';
      addUserBtn.onclick = ()=>{
        addForm.reset();
        addForm.project_id.value = p.id;
        addModal.style.display='flex';
      };
      closeAdd.onclick = hideAdd;
      cancelAdd.onclick = hideAdd;
      addModal.addEventListener('click', e=>{ if(e.target===addModal) hideAdd(); });

      addForm.onsubmit = e=>{
        e.preventDefault();
        const data = new URLSearchParams(new FormData(addForm));
        data.append('action','add_user');
        api('../api/project_users.php', {method:'POST', body:data})
          .then(res=>{
            if(res.success){ hideAdd(); showToast('Ajouté'); usersDiv.dataset.loaded='0'; loadUsers(); }
            else showToast('Erreur');
          });
      };
    }
  });

  /* ---------- Modal création projet ---------- */
  const createBtn   = document.getElementById('create-project-btn');
  const createModal = document.getElementById('createProjectModal');
  const createForm  = document.getElementById('create-form');
  if(createBtn && createModal && createForm){
    const closeCreate = createModal.querySelector('.modal-close');
    const cancelCreate = createModal.querySelector('.cancel-btn');
    const addrInput = createForm.querySelector('[name="address"]');
    const latInput  = createForm.querySelector('[name="lat"]');
    const lngInput  = createForm.querySelector('[name="lng"]');

    /* Map */
    const defaultLatLng = [48.8566,2.3522];
    let map=null, marker=null, debounceId=null;

    const initMap = () => {
      if(map){ map.invalidateSize(); return; }
      map = L.map('create-map').setView(defaultLatLng, 13);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
      marker = L.marker(defaultLatLng, {draggable:true}).addTo(map);
      marker.on('dragend', () => {
        const {lat,lng} = marker.getLatLng();
        latInput.value=lat; lngInput.value=lng;
        reverseGeocode(lat,lng);
      });
    };

    const geocode = q=>{
      if(!q) return;
      fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=1`, {
        headers:{'Accept':'application/json','User-Agent':'Batitrax/1.0'}
      }).then(r=>r.json())
      .then(d=>{
        if(!d.length) return;
        const lat=parseFloat(d[0].lat), lon=parseFloat(d[0].lon);
        map.setView([lat,lon],17);
        marker.setLatLng([lat,lon]);
        latInput.value=lat; lngInput.value=lon;
      });
    };

    const reverseGeocode = (lat,lng)=>{
      fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
        headers:{'Accept':'application/json','User-Agent':'Batitrax/1.0'}
      }).then(r=>r.json())
      .then(d=>{ if(d && d.display_name){ addrInput.value=d.display_name; }});
    };

    /* Emoji picker */
    const selectedEmoji = document.getElementById('selectedEmoji');
    const emojiGrid = document.getElementById('emojiGrid');
    const emojiHidden = createForm.querySelector('[name="emoji"]');
    selectedEmoji.onclick = ()=> emojiGrid.style.display = emojiGrid.style.display==='grid' ? 'none' : 'grid';
    emojiGrid.querySelectorAll('.emoji-item').forEach(item=>{
      item.onclick = ()=>{
        selectedEmoji.textContent = item.dataset.emoji;
        emojiHidden.value = item.dataset.emoji;
        emojiGrid.style.display='none';
      };
    });
    document.addEventListener('click', e=>{
      if(!e.target.closest('.emoji-picker')) emojiGrid.style.display='none';
    });

    /* Show/hide modal */
    const hideCreate = () => createModal.style.display='none';
    const resetCreate = ()=>{
      createForm.reset();
      selectedEmoji.textContent='🏗️';
      emojiHidden.value='🏗️';
      latInput.value=defaultLatLng[0]; lngInput.value=defaultLatLng[1];
      if(marker){ marker.setLatLng(defaultLatLng); map.setView(defaultLatLng,13); }
    };
    createBtn.onclick = ()=>{ resetCreate(); createModal.style.display='flex'; setTimeout(initMap,50); };
    closeCreate.onclick = hideCreate; cancelCreate.onclick = hideCreate;
    createModal.addEventListener('click', e=>{ if(e.target===createModal) hideCreate(); });

    addrInput.addEventListener('input', ()=>{
      clearTimeout(debounceId);
      debounceId = setTimeout(()=> geocode(addrInput.value.trim()), 600);
    });
  }

});
</script>
