<?php
// src/db.php
try {
    $dbFile = getenv('R2_DB_FILE');
    if ($dbFile === false || trim($dbFile) === '') {
        $dbFile = __DIR__ . '/../data/database.sqlite';
    }

    $dbDirectory = dirname($dbFile);
    if (!is_dir($dbDirectory)) {
        mkdir($dbDirectory, 0777, true);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
