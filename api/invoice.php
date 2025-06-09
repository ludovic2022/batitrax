<?php
session_start();
require_once 'config.php';
require_once __DIR__ . '/libs/fpdf.php';
$conn = getConnection();

$action = $_REQUEST['action'] ?? '';
if ($action === 'generate') {
    // Only superadmin and admin can generate on demand
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $role = $stmt->fetchColumn();
    if (($role === 'superadmin' || $role === 'admin') && isset($_POST['account_id'])) {
        $accountId = intval($_POST['account_id']);
        $today = new DateTime();
        // Count users
        $cstmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE account_id = ?");
        $cstmt->execute([$accountId]);
        $userCount = $cstmt->fetchColumn();
        // Price per user
        $pstmt = $conn->prepare("SELECT price_per_user FROM accounts WHERE id = ?");
        $pstmt->execute([$accountId]);
        $pricePerUser = $pstmt->fetchColumn();
        $total = $userCount * $pricePerUser;
        // Generate PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10,'Facture Batitrax',0,1,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Ln(5);
        $stmtInfo = $conn->prepare("SELECT name FROM accounts WHERE id = ?");
        $stmtInfo->execute([$accountId]);
        $accountName = $stmtInfo->fetchColumn();
        $pdf->Cell(0,10,'Compte: '.$accountName,0,1);
        $pdf->Cell(0,10,'Date: '.$today->format('Y-m-d'),0,1);
        $pdf->Ln(5);
        $pdf->Cell(0,10,'Nombre d\'utilisateurs: '.$userCount,0,1);
        $pdf->Cell(0,10,'Prix par utilisateur: '.number_format($pricePerUser,2,',',' ').' €',0,1);
        $pdf->Cell(0,10,'Total: '.number_format($total,2,',',' ').' €',0,1);
        // Save file
        $dir = __DIR__.'/../invoices/'.$accountId.'/'.$today->format('Y');
        if (!is_dir($dir)) mkdir($dir,0777,true);
        $filename = $dir.'/'.$today->format('Y-m').'.pdf';
        $pdf->Output('F', $filename);
        header('Location: ../Batitrax/dashboard.php?view=invoices&view_account='.$accountId);
        exit;
    }
}
header('Location: ../Batitrax/dashboard.php');
?>