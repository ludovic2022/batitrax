<?php
session_start();
require_once 'config.php';
$conn = getConnection();
$action = $_REQUEST['action'] ?? '';

header('Content-Type: application/json');

if ($action === 'update_user') {
    // Permission check
    $stmt = $conn->prepare("SELECT role, account_id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$current) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur courant introuvable.']);
        exit;
    }
    $targetId = intval($_POST['user_id']);
    // Fetch target user's account_id
    $tstmt = $conn->prepare("SELECT account_id FROM users WHERE id = ?");
    $tstmt->execute([$targetId]);
    $targetAcc = $tstmt->fetchColumn();
    if (!$targetAcc) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur cible introuvable.']);
        exit;
    }
    // Only superadmin or same account admin can update
    if ($current['role'] !== 'superadmin' && $current['account_id'] != $targetAcc) {
        echo json_encode(['success' => false, 'message' => 'Permission refusée.']);
        exit;
    }
    // Update fields
    $fields = ['first_name', 'last_name', 'email', 'phone', 'role'];
    $updates = [];
    $params = [];
    foreach ($fields as $f) {
        if (!isset($_POST[$f])) {
            echo json_encode(['success' => false, 'message' => "Champ {$f} manquant."]);
            exit;
        }
        $updates[] = "{$f} = ?";
        $params[] = $_POST[$f];
    }
    $params[] = $targetId;
    $usql = "UPDATE users SET " . implode(',', $updates) . " WHERE id = ?";
    $ustmt = $conn->prepare($usql);
    $ustmt->execute($params);
    echo json_encode(['success' => true]);
    exit;
}

// Other actions are non-JSON, preserve existing behavior
header('Content-Type: text/html');

// ... existing create_user, delete_user, create_account, etc.
switch ($action) {
    case 'delete_user':
        // Existing delete_user logic...
        $userId = intval($_POST['user_id']);
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        header('Location: ../Batitrax/dashboard.php?view=users');
        break;
    case 'create_user':
        // Existing create_user logic...
        // ...
        break;
    // Add other cases here...
    default:
        header('Location: ../Batitrax/dashboard.php');
}
?>