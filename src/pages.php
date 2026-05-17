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
    if (!in_array('page_type', $columns, true)) {
        $pdo->exec("ALTER TABLE pages ADD COLUMN page_type TEXT DEFAULT 'internal'");
    }
    if (!in_array('external_url', $columns, true)) {
        $pdo->exec("ALTER TABLE pages ADD COLUMN external_url TEXT");
    }
    if (!in_array('sort_order', $columns, true)) {
        $pdo->exec("ALTER TABLE pages ADD COLUMN sort_order INTEGER DEFAULT 0");
    }
}

function getAllPages() {
    ensurePagesSchema();
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM pages ORDER BY sort_order ASC, id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updatePagesOrder($orderedIds) {
    ensurePagesSchema();
    global $pdo;
    $stmt = $pdo->prepare("UPDATE pages SET sort_order = ? WHERE id = ?");
    foreach ($orderedIds as $index => $id) {
        $stmt->execute([$index, (int)$id]);
    }
    return true;
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
    $content = $data['content'] ?? '';
    $content_pt = $data['content_pt'] ?? '';
    $page_type = ($data['page_type'] ?? '') === 'external' ? 'external' : 'internal';
    $external_url = trim($data['external_url'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO pages (title, title_pt, slug, content, content_pt, page_type, external_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $title_pt, $slug, $content, $content_pt, $page_type, $external_url])) {
        return (int)$pdo->lastInsertId();
    }
    return false;
}

function updatePage($id, $data) {
    ensurePagesSchema();
    global $pdo;
    $title = $data['title'];
    $title_pt = $data['title_pt'] ?? '';
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $content = $data['content'] ?? '';
    $content_pt = $data['content_pt'] ?? '';
    $page_type = ($data['page_type'] ?? '') === 'external' ? 'external' : 'internal';
    $external_url = trim($data['external_url'] ?? '');

    $stmt = $pdo->prepare("UPDATE pages SET title = ?, title_pt = ?, slug = ?, content = ?, content_pt = ?, page_type = ?, external_url = ? WHERE id = ?");
    return $stmt->execute([$title, $title_pt, $slug, $content, $content_pt, $page_type, $external_url, (int)$id]);
}

function deletePage($id) {
    ensurePagesSchema();
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    return $stmt->execute([$id]);
}
