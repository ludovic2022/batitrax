<?php if(!defined('BATITRAX')) exit; ?>
<section id="view-chat" class="view <?= $view==='chat'?'active':'' ?>">
  <div class="card"><h3>Chat</h3>
    <?php if($selectedProject): ?>
      <div class="messages"><?php $ms=$conn->prepare("SELECT m.content,m.created_at,u.email FROM messages m JOIN users u ON m.user_id=u.id WHERE project_id=?"); $ms->execute([$selectedProject]); foreach($ms->fetchAll(PDO::FETCH_ASSOC) as $m): ?><div class="message <?=($m['email']===$user['email'])?'self':'other'?>"><strong><?=htmlspecialchars($m['email'])?></strong><br><?=nl2br(htmlspecialchars($m['content']))?><br><small><?=htmlspecialchars($m['created_at'])?></small></div><?php endforeach;?></div>
      <div class="chat-input"><form method="post" action="../api/project.php?action=send_message&project_id=<?=$selectedProject?>"><input name="content" placeholder="Votre message…" required><button>Envoyer</button></form></div>
    <?php else: ?><p>Sélectionnez un projet.</p><?php endif; ?>
  </div>
</section>
