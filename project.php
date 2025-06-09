<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) exit('Unauthorized');
$conn = getConnection();
$action = $_GET['action'] ?? '';
$stmt = $conn->prepare("SELECT role, account_id FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

switch($action) {
    case 'create_project':
        if ($user['role']==='admin') {
            $name = $_POST['name'] ?? '';
            if ($name) {
                $stmt=$conn->prepare("INSERT INTO projects(name, account_id) VALUES(?,?)");
                $stmt->execute([$name,$user['account_id']]);
            }
        }
        break;
    case 'send_message':
        $pid = $_GET['project_id'];
        $content = $_POST['content'] ?? '';
        if ($content) {
            $stmt=$conn->prepare("INSERT INTO messages(project_id, user_id, content) VALUES(?,?,?)");
            $stmt->execute([$pid,$_SESSION['user_id'],$content]);
        }
        break;
}
header('Location: ../Batitrax/dashboard.php'.(isset($pid)?"?project_id=$pid":""));
?>