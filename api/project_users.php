<?php
declare(strict_types=1);
require_once __DIR__.'/../config/init.php'; // DB connection in $conn + session

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

$user = $_SESSION['user'] ?? null;
if(!$user){ echo json_encode(['success'=>false,'message'=>'Unauthenticated']); exit; }

function isAdmin($user){ return isset($user['role']) && $user['role']==='admin'; }

/**
 * Quick helper : check that project belongs to same account
 */
function projectBelongs(PDO $conn,int $projectId,int $accountId): bool{
  $stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE id=? AND account_id=?");
  $stmt->execute([$projectId,$accountId]);
  return $stmt->fetchColumn()>0;
}

switch($action){
  case 'list_users':
    $projectId = intval($_GET['project_id']??0);
    if(!$projectId){ echo json_encode(['success'=>false,'message'=>'project_id required']); exit; }
    if(!projectBelongs($conn,$projectId,$user['account_id'])){ echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
    $sql = "SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS fullname, pu.role
            FROM project_users pu
            JOIN users u ON u.id = pu.user_id
            WHERE pu.project_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$projectId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'users'=>$rows]); exit;

  case 'add_user':
    if(!isAdmin($user)){ echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
    $projectId = intval($_POST['project_id']??0);
    $userId    = intval($_POST['user_id']??0);
    $role = trim($_POST['role'] ?? 'viewer');
    if(!$projectId||!$userId){ echo json_encode(['success'=>false,'message'=>'params']); exit; }
    if(!projectBelongs($conn,$projectId,$user['account_id'])){ echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
    $stmt = $conn->prepare("INSERT IGNORE INTO project_users(project_id,user_id,role) VALUES(?,?,?)");
    $ok = $stmt->execute([$projectId,$userId,$role]);
    echo json_encode(['success'=>$ok]); exit;

  case 'update_user':
    if(!isAdmin($user)){ echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
    $projectId = intval($_POST['project_id']??0);
    $userId    = intval($_POST['user_id']??0);
    $role = trim($_POST['role'] ?? 'viewer');
    if(!$projectId||!$userId){ echo json_encode(['success'=>false,'message'=>'params']); exit; }
    if(!projectBelongs($conn,$projectId,$user['account_id'])){ echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
    $stmt = $conn->prepare("UPDATE project_users SET role=? WHERE project_id=? AND user_id=?");
    $ok = $stmt->execute([$role,$projectId,$userId]);
    echo json_encode(['success'=>$ok]); exit;

  case 'delete_user':
    if(!isAdmin($user)){ echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
    $projectId = intval($_POST['project_id']??0);
    $userId    = intval($_POST['user_id']??0);
    if(!$projectId||!$userId){ echo json_encode(['success'=>false,'message'=>'params']); exit; }
    if(!projectBelongs($conn,$projectId,$user['account_id'])){ echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
    $stmt = $conn->prepare("DELETE FROM project_users WHERE project_id=? AND user_id=?");
    $ok = $stmt->execute([$projectId,$userId]);
    echo json_encode(['success'=>$ok]); exit;

  default:
    echo json_encode(['success'=>false,'message'=>'Unknown action']); exit;
}
