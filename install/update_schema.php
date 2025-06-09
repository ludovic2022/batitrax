<?php
// install/update_schema.php

// Ajustez le chemin vers config.php
require_once __DIR__ . '/../api/config.php';

try {
    $conn = getConnection();

    $sql = "
    ALTER TABLE projects
        ADD COLUMN emoji VARCHAR(10)     DEFAULT NULL,
        ADD COLUMN address VARCHAR(255)  DEFAULT NULL,
        ADD COLUMN lat DECIMAL(10,8)     DEFAULT NULL,
        ADD COLUMN lng DECIMAL(11,8)     DEFAULT NULL,
        ADD COLUMN manager_id INT        DEFAULT NULL
    ";
    $conn->exec($sql);

    echo 'Schema mis à jour avec succès.';
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Erreur lors de la mise à jour du schéma : ' . htmlspecialchars($e->getMessage());
}
