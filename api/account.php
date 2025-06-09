<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) exit('Unauthorized');
$conn = getConnection();
$stmt = $conn->prepare("SELECT role,account_id FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$action = $_GET['action'] ?? '';

switch($action) {
    case 'create_account':
        if ($user['role']==='superadmin') {
            $name = $_POST['name'] ?? '';
            $price = $_POST['price_per_user'] ?? 0;
            if ($name) {
                $stmt = $conn->prepare("INSERT INTO accounts(name,price_per_user) VALUES(?,?)");
                $stmt->execute([$name,$price]);
            }
        }
        break;
    case 'update_account_price':
        if ($user['role']==='superadmin') {
            $aid = $_POST['account_id'];
            $price = $_POST['price_per_user'] ?? 0;
            $stmt = $conn->prepare("UPDATE accounts SET price_per_user=? WHERE id=?");
            $stmt->execute([$price,$aid]);
        }
        break;
    case 'create_admin':
        if ($user['role']==='superadmin') {
            $aid = $_POST['account_id'];
            $email = $_POST['email'];
            $pwd = $_POST['password'];
            $h = password_hash($pwd,PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users(first_name,last_name,email,password,role,phone,account_id) VALUES('','',?,?,'admin',NULL,?)");
            $stmt->execute([$email,$h,$aid]);
            mail($email,"Votre compte Batitrax","Email: $email\nMDP: $pwd","From:no-reply@batitrax.menuiserie-rieu.fr");
        }
        break;
    case 'create_user':
        if (in_array($user['role'],['superadmin','admin'])) {
            $fn = $_POST['first_name'] ?? '';
            $ln = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $role2 = $_POST['role'] ?? 'salarie';
            $pwd = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'),0,8);
            $h = password_hash($pwd,PASSWORD_DEFAULT);
            $aid = ($user['role']==='superadmin'?$_POST['account_id']:$user['account_id']);
            $stmt = $conn->prepare("INSERT INTO users(first_name,last_name,email,password,role,phone,account_id) VALUES(?,?,?,?,?,?,?)");
            $stmt->execute([$fn,$ln,$email,$h,$role2,$phone,$aid]);
            mail($email,"Votre compte Batitrax","Bonjour $fn $ln,\nEmail: $email\nMDP: $pwd","From:no-reply@batitrax.menuiserie-rieu.fr");
        }
        break;
    case 'rename_account':
        if ($user['role']==='admin') {
            $new = $_POST['new_name'];
            $stmt = $conn->prepare("UPDATE accounts SET name=? WHERE id=?");
            $stmt->execute([$new,$user['account_id']]);
        }
        break;
    case 'delete_account':
        if ($user['role']==='superadmin') {
            $aid = $_POST['account_id'];
            $stmt = $conn->prepare("DELETE FROM accounts WHERE id=?");
            $stmt->execute([$aid]);
        }
        break;
    case 'delete_user':
        if (in_array($user['role'],['superadmin','admin'])) {
            $uid = $_POST['user_id'];
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->execute([$uid]);
        }
        break;
    case 'change_password_user':
        if ($user['role']==='superadmin') {
            $uid = $_POST['user_id'];
            $new = $_POST['new_password'];
            $em = $conn->prepare("SELECT email FROM users WHERE id=?");
            $em->execute([$uid]); $mto = $em->fetchColumn();
            $h = password_hash($new,PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->execute([$h,$uid]);
            mail($mto,"MDP Batitrax","Votre MDP: $new","From:no-reply@batitrax.menuiserie-rieu.fr");
        }
        break;
    case 'change_user_password':
        if ($user['role']==='admin') {
            $uid = $_POST['user_id'];
            $new = $_POST['new_password'];
            $em = $conn->prepare("SELECT email FROM users WHERE id=?");
            $em->execute([$uid]); $mto = $em->fetchColumn();
            $h = password_hash($new,PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->execute([$h,$uid]);
            mail($mto,"MDP Batitrax","Votre MDP: $new","From:no-reply@batitrax.menuiserie-rieu.fr");
        }
        break;
}
header('Location: ../Batitrax/dashboard.php');