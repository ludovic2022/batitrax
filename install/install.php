<?php
require_once __DIR__.'/../api/config.php';
$conn = getConnection();
$sql = "CREATE TABLE IF NOT EXISTS accounts(
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(255) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->exec($sql);
$sql = "CREATE TABLE IF NOT EXISTS users(
 id INT AUTO_INCREMENT PRIMARY KEY,
 email VARCHAR(255) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('superadmin','admin','salarie','externe','encadrant') NOT NULL,
 account_id INT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->exec($sql);
// Insert superadmin
$email='ludovic.riquier@gmail.com';
$password='root';
$hash=password_hash($password,PASSWORD_DEFAULT);
$stmt=$conn->prepare("SELECT COUNT(*) FROM users WHERE email=?");
$stmt->execute([$email]);
if($stmt->fetchColumn()==0){
    $stmt=$conn->prepare("INSERT INTO users(email,password,role) VALUES(?,?, 'superadmin')");
    $stmt->execute([$email,$hash]);
}
echo "Installation complete.";
?>