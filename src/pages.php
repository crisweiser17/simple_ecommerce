<?php

function getAllPages() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM pages ORDER BY title ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPage($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPageBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createPage($data) {
    global $pdo;
    $title = $data['title'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $content = $data['content'];
    
    $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content) VALUES (?, ?, ?)");
    return $stmt->execute([$title, $slug, $content]);
}

function updatePage($id, $data) {
    global $pdo;
    $title = $data['title'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $content = $data['content'];
    
    $stmt = $pdo->prepare("UPDATE pages SET title = ?, slug = ?, content = ? WHERE id = ?");
    return $stmt->execute([$title, $slug, $content, $id]);
}

function deletePage($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    return $stmt->execute([$id]);
}
