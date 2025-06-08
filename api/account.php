<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) exit('Unauthorized.');
$conn = getConnection();
$stmt = $conn->prepare("SELECT role, account_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$action = $_GET['action'] ?? '';
switch($action) {
    case 'create_account':
        if($user['role']=='superadmin'){
            $name = $_POST['name'] ?? '';
            if($name){
                $stmt = $conn->prepare("INSERT INTO accounts(name) VALUES(?)");
                $stmt->execute([$name]);
            }
        }
        break;
    case 'create_admin':
        if($user['role']=='superadmin'){
            $account_id = $_POST['account_id'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $hash = password_hash($password,PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users(email,password,role,account_id) VALUES(?,?, 'admin',?)");
            $stmt->execute([$email,$hash,$account_id]);
        }
        break;
    case 'create_user':
        if(in_array($user['role'], ['superadmin','admin'])){
            $email = $_POST['email'];
            $password = $_POST['password'];
            $role = $_POST['role'];
            $hash = password_hash($password,PASSWORD_DEFAULT);
            if($user['role']=='superadmin'){
                $acct_id = $_POST['account_id'];
            } else {
                $acct_id = $user['account_id'];
            }
            $stmt = $conn->prepare("INSERT INTO users(email,password,role,account_id) VALUES(?,?,?,?)");
            $stmt->execute([$email,$hash,$role,$acct_id]);
        }
        break;
    case 'delete_account':
        if($user['role']=='superadmin'){
            $id = $_POST['account_id'];
            $stmt = $conn->prepare("DELETE FROM accounts WHERE id=?");
            $stmt->execute([$id]);
        }
        break;
    case 'delete_user':
        if(in_array($user['role'],['superadmin','admin'])){
            $uid = $_POST['user_id'];
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->execute([$uid]);
        }
        break;
    case 'change_password_user':
        if($user['role']=='superadmin'){
            $uid = $_POST['user_id'];
            $new = $_POST['new_password'];
            $hash = password_hash($new,PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->execute([$hash,$uid]);
        }
        break;
    // ... autres cases existants ...
}
header('Location: ../Batitrax/dashboard.php');
?>