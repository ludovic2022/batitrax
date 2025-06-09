<?php session_start();
if(isset($_SESSION['user_id'])) header('Location: dashboard.php');
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Batitrax - Connexion</title><style>
body{font-family:Arial,sans-serif;background:#f9f9f9;display:flex;justify-content:center;align-items:center;height:100vh;}
.login-container{background:#fff;padding:2rem;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.1);text-align:center;}
.login-container input{display:block;width:100%;margin:0.5rem 0;padding:0.5rem;}
.login-container button{padding:0.5rem 1rem;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer;}
</style></head>
<body>
<div class="login-container">
  <h1>Batitrax</h1>
  <form method="post" action="../api/auth.php?action=login">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button>Connexion</button>
  </form>
  <?php if(isset($_GET['error'])) echo '<p style="color:red">'.htmlspecialchars($_GET['error']).'</p>'; ?>
</div>
</body>
</html>