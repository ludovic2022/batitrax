<?php
session_start();
require_once 'config.php';
$conn = getConnection();
$action = $_REQUEST['action'] ?? '';

// Toujours renvoyer JSON pour update/delete
if (in_array($action, ['update_project', 'delete_project'])) {
    header('Content-Type: application/json');
    // Vérification authentification
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success'=>false,'message'=>'Non authentifié']);
        exit;
    }
    // Récupère le rôle + account_id
    $ust = $conn->prepare("SELECT role, account_id FROM users WHERE id = ?");
    $ust->execute([$_SESSION['user_id']]);
    $user = $ust->fetch(PDO::FETCH_ASSOC);

    $project_id = intval($_POST['project_id']);
    // Récupère l’account du projet
    $pst = $conn->prepare("SELECT account_id FROM projects WHERE id = ?");
    $pst->execute([$project_id]);
    $proj_acc = $pst->fetchColumn();

    // Permissions : superadmin ou admin du même compte
    if (!$proj_acc || ($user['role'] !== 'superadmin' && $proj_acc != $user['account_id'])) {
        echo json_encode(['success'=>false,'message'=>'Permission refusée']);
        exit;
    }

    if ($action === 'update_project') {
        // Champs modifiables
        $fields = ['name','emoji','address','manager_id'];
        $updates = []; $params = [];
        foreach ($fields as $f) {
            if (isset($_POST[$f])) {
                $updates[] = "$f = ?";
                $params[]  = $_POST[$f];
            }
        }
        if (empty($updates)) {
            echo json_encode(['success'=>false,'message'=>'Aucun champ à jour']);
            exit;
        }
        $params[] = $project_id;
        $sql = "UPDATE projects SET " . implode(',', $updates) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action === 'delete_project') {
        $d = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $d->execute([$project_id]);
        echo json_encode(['success'=>true]);
        exit;
    }
}

// Pour create_project on conserve l’ancien comportement redirection
if ($action === 'create_project') {
    // (Insérez ici votre code existant de création de projet)
    // Exemple minimal :
    $userStmt = $conn->prepare("SELECT role, account_id FROM users WHERE id=?");
    $userStmt->execute([$_SESSION['user_id']]);
    $u = $userStmt->fetch(PDO::FETCH_ASSOC);
    $account_id = $u['role']==='admin' ? $u['account_id'] : $_POST['account_id'];
    $stmt = $conn->prepare("INSERT INTO projects (account_id,name,emoji,address,lat,lng,manager_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
      $account_id,
      $_POST['name'],
      $_POST['emoji'],
      $_POST['address'],
      $_POST['lat'],
      $_POST['lng'],
      $_POST['manager_id']
    ]);
    header('Location: ../Batitrax/dashboard.php?view=projects');
    exit;
}

// Redirection par défaut
header('Location: ../Batitrax/dashboard.php');
exit;
?>
