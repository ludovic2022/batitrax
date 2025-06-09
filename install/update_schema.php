<?php
require_once __DIR__.'/../api/config.php';
$conn = getConnection();

// Add price_per_user if missing
try { $conn->exec("ALTER TABLE accounts ADD COLUMN IF NOT EXISTS price_per_user DECIMAL(10,2) NOT NULL DEFAULT 0"); } catch(PDOException $e) {}

// Add user fields if not exist
$columns = array_column($conn->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC), 'Field');
if (!in_array('first_name',$columns)) $conn->exec("ALTER TABLE users ADD COLUMN first_name VARCHAR(100) DEFAULT NULL");
if (!in_array('last_name',$columns))  $conn->exec("ALTER TABLE users ADD COLUMN last_name  VARCHAR(100) DEFAULT NULL");
if (!in_array('phone',$columns))      $conn->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL");

// Ensure tables exist
$conn->exec("CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price_per_user DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8mb4;");

$conn->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name  VARCHAR(100),
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin','admin','salarie','externe','encadrant') NOT NULL,
    phone VARCHAR(20),
    account_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4;");

$conn->exec("CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4;");

$conn->exec("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4;");

// Ensure superadmin exists
$email = 'ludovic.riquier@gmail.com';
$password = 'root';
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email=?");
$stmt->execute([$email]);
if ($stmt->fetchColumn() == 0) {
    $stmt = $conn->prepare("INSERT INTO users(first_name,last_name,email,password,role) VALUES('Super','Admin',?,?, 'superadmin')");
    $stmt->execute([$email,$hash]);
}

echo 'Schéma mis à jour.';
?>