<?php

function ensurePagesSchema() {
    global $pdo;
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        content TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $pdo->query("PRAGMA table_info(pages)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (!in_array('title_pt', $columns, true)) {
        $pdo->exec("ALTER TABLE pages ADD COLUMN title_pt TEXT");
    }
    if (!in_array('content_pt', $columns, true)) {
        $pdo->exec("ALTER TABLE pages ADD COLUMN content_pt TEXT");
    }
}

function getAllPages() {
    ensurePagesSchema();
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM pages ORDER BY title ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPage($id) {
    ensurePagesSchema();
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPageBySlug($slug) {
    ensurePagesSchema();
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createPage($data) {
    ensurePagesSchema();
    global $pdo;
    $title = $data['title'];
    $title_pt = $data['title_pt'] ?? '';
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $content = $data['content'];
    $content_pt = $data['content_pt'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO pages (title, title_pt, slug, content, content_pt) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$title, $title_pt, $slug, $content, $content_pt]);
}

function updatePage($id, $data) {
    ensurePagesSchema();
    global $pdo;
    $title = $data['title'];
    $title_pt = $data['title_pt'] ?? '';
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $content = $data['content'];
    $content_pt = $data['content_pt'] ?? '';
    
    $stmt = $pdo->prepare("UPDATE pages SET title = ?, title_pt = ?, slug = ?, content = ?, content_pt = ? WHERE id = ?");
    return $stmt->execute([$title, $title_pt, $slug, $content, $content_pt, (int)$id]);
}

function deletePage($id) {
    ensurePagesSchema();
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    return $stmt->execute([$id]);
}
