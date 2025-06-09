<?php
// install/update_schema.php
// Met à jour la base de données Batitrax (ajout de project_users et éventuels champs)

require_once __DIR__ . '/../api/config.php';

try {
    $conn = getConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1) Table project_users
    $conn->exec("
        CREATE TABLE IF NOT EXISTS project_users (
            project_id INT NOT NULL,
            user_id    INT NOT NULL,
            role       VARCHAR(20) DEFAULT 'viewer',
            PRIMARY KEY (project_id, user_id),
            CONSTRAINT fk_pu_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
            CONSTRAINT fk_pu_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2) Colonnes supplémentaires sur projects
    $needed = [
        'emoji'      => "ALTER TABLE projects ADD COLUMN emoji VARCHAR(10) DEFAULT NULL",
        'address'    => "ALTER TABLE projects ADD COLUMN address VARCHAR(255) DEFAULT NULL",
        'lat'        => "ALTER TABLE projects ADD COLUMN lat DECIMAL(10,8) DEFAULT NULL",
        'lng'        => "ALTER TABLE projects ADD COLUMN lng DECIMAL(11,8) DEFAULT NULL",
        'manager_id' => "ALTER TABLE projects ADD COLUMN manager_id INT DEFAULT NULL"
    ];
    $existing = $conn->query("DESCRIBE projects")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($needed as $col => $sql) {
        if (!in_array($col, $existing)) {
            $conn->exec($sql);
        }
    }

    echo 'Schéma mis à jour avec succès.';
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Erreur lors de la mise à jour du schéma : ' . htmlspecialchars($e->getMessage());
}
