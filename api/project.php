<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
$conn = getConnection();
$stmt = $conn->prepare("SELECT role, account_id FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$action = $_GET['action'] ?? '';
header('Content-Type: application/json');
if ($action == 'create_project' && $user['role']=='admin') {
    $name = $_POST['name'] ?? '';
    $stmt = $conn->prepare("INSERT INTO projects(name,account_id) VALUES(?,?)");
    $stmt->execute([$name,$user['account_id']]);
    echo json_encode(['status'=>'ok']);
} elseif ($action == 'get_projects') {
    if($user['role']=='superadmin'){
        $data = $conn->query("SELECT * FROM projects")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT * FROM projects WHERE account_id=?");
        $stmt->execute([$user['account_id']]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode($data);
} elseif ($action == 'send_message') {
    $pid = $_GET['project_id'];
    $content = $_POST['content'] ?? '';
    $stmt = $conn->prepare("INSERT INTO messages(project_id,user_id,content) VALUES(?,?,?)");
    $stmt->execute([$pid,$_SESSION['user_id'],$content]);
    echo json_encode(['status'=>'ok']);
} elseif ($action == 'get_messages') {
    $pid = $_GET['project_id'];
    $stmt = $conn->prepare("SELECT m.content,m.created_at,u.email FROM messages m JOIN users u ON m.user_id=u.id WHERE project_id=? ORDER BY m.created_at");
    $stmt->execute([$pid]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
}
?>