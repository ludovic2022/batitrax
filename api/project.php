<?php
session_start();
require_once 'config.php';
$conn = getConnection();
$action = $_REQUEST['action'] ?? '';

// AJAX responses for update/delete
if (in_array($action, ['update_project', 'delete_project'])) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success'=>false,'message'=>'Non authentifié']);
        exit;
    }
    // Récupère le rôle + account_id
    $ust = $conn->prepare("SELECT role, account_id FROM users WHERE id = ?");
    $ust->execute([$_SESSION['user_id']]);
    $user = $ust->fetch(PDO::FETCH_ASSOC);

    // Validation project_id
    $project_id = intval($_POST['project_id'] ?? 0);
    if (!$project_id) {
        echo json_encode(['success'=>false,'message'=>'ID projet manquant']);
        exit;
    }

    // Vérification existence et permissions
    $pst = $conn->prepare("SELECT account_id FROM projects WHERE id = ?");
    $pst->execute([$project_id]);
    $proj_acc = $pst->fetchColumn();
    if (!$proj_acc || ($user['role'] !== 'superadmin' && $proj_acc != $user['account_id'])) {
        echo json_encode(['success'=>false,'message'=>'Permission refusée']);
        exit;
    }

    if ($action === 'update_project') {
        // Mise à jour via POST
        $sql = "UPDATE projects SET name=?, emoji=?, address=?, lat=?, lng=?, manager_id=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $_POST['name'] ?? '',
            $_POST['emoji'] ?? '',
            $_POST['address'] ?? '',
            $_POST['lat'] ?? 0,
            $_POST['lng'] ?? 0,
            $_POST['manager_id'] ?? null,
            $project_id
        ]);
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action === 'delete_project') {
        // Suppression du projet
        $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        echo json_encode(['success'=>true]);
        exit;
    }
}

// Redirection par défaut
header('Location: ../Batitrax/dashboard.php');
exit;
?>
