<?php
function getConnection() {
    $host='localhost';
    $db='wilj6610_batitrax32390';
    $user='wilj6610_batitrax32390';
    $pass='PASSWORDdelamortQUITUE123!';
    $dsn="mysql:host=$host;dbname=$db;charset=utf8mb4";
    try {
        $pdo=new PDO($dsn,$user,$pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: ".$e->getMessage());
    }
}
?>