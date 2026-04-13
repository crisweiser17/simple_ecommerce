<?php

function getDbConnectionBlog() {
    $dbFile = __DIR__ . '/../data/database.sqlite';
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function ensureBlogSchema() {
    $pdo = getDbConnectionBlog();
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        image_url TEXT,
        content TEXT,
        status TEXT DEFAULT 'published',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

function getAllBlogPosts($status = null) {
    $pdo = getDbConnectionBlog();
    if ($status) {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE status = ? ORDER BY created_at DESC");
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBlogPostById($id) {
    $pdo = getDbConnectionBlog();
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getBlogPostBySlug($slug) {
    $pdo = getDbConnectionBlog();
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function saveBlogPost($data) {
    $pdo = getDbConnectionBlog();
    
    $id = $data['id'] ?? null;
    $title = trim((string)($data['title'] ?? ''));
    $slug = trim((string)($data['slug'] ?? ''));
    $imageUrl = trim((string)($data['image_url'] ?? ''));
    $content = $data['content'] ?? '';
    $status = $data['status'] ?? 'published';
    
    if (!$title) {
        throw new Exception("Title is required.");
    }
    if (!$slug) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    
    if ($id) {
        $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, slug = ?, image_url = ?, content = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$title, $slug, $imageUrl, $content, $status, $id]);
        return $id;
    } else {
        $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, image_url, content, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $imageUrl, $content, $status]);
        return $pdo->lastInsertId();
    }
}

function deleteBlogPost($id) {
    $pdo = getDbConnectionBlog();
    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->execute([$id]);
}
