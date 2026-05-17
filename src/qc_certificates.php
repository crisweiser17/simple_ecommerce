<?php
require_once __DIR__ . '/db.php';

function ensureQcCertificatesSchema() {
    static $checked = false;
    if ($checked) {
        return;
    }
    global $pdo;

    $pdo->exec("CREATE TABLE IF NOT EXISTS qc_certificates (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        product_name TEXT,
        product_id INTEGER,
        batch_number TEXT,
        issued_at DATE,
        pdf_url TEXT NOT NULL,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_qc_certificates_code ON qc_certificates(code)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_qc_certificates_product ON qc_certificates(product_id)");

    $checked = true;
}

function normalizeQcCode($input) {
    $code = strtoupper(trim((string)$input));
    $code = preg_replace('/[^A-Z0-9\-]/', '', $code);
    return $code;
}

function getQcCertificateByCode($code) {
    ensureQcCertificatesSchema();
    global $pdo;

    $normalized = normalizeQcCode($code);
    if ($normalized === '') {
        return false;
    }

    $stmt = $pdo->prepare("SELECT * FROM qc_certificates WHERE UPPER(code) = ? LIMIT 1");
    $stmt->execute([$normalized]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllQcCertificates() {
    ensureQcCertificatesSchema();
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM qc_certificates ORDER BY issued_at DESC, code DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getQcCertificate($id) {
    ensureQcCertificatesSchema();
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM qc_certificates WHERE id = ?");
    $stmt->execute([(int)$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function saveQcCertificate(array $data, $id = null) {
    ensureQcCertificatesSchema();
    global $pdo;

    $code = normalizeQcCode($data['code'] ?? '');
    if ($code === '') {
        throw new InvalidArgumentException('Certificate code is required.');
    }
    $productName = trim((string)($data['product_name'] ?? ''));
    $productId = isset($data['product_id']) && $data['product_id'] !== '' ? (int)$data['product_id'] : null;
    $batchNumber = trim((string)($data['batch_number'] ?? ''));
    $issuedAt = trim((string)($data['issued_at'] ?? ''));
    if ($issuedAt === '') $issuedAt = null;
    $pdfUrl = trim((string)($data['pdf_url'] ?? ''));
    $notes = trim((string)($data['notes'] ?? ''));

    if ($pdfUrl === '') {
        throw new InvalidArgumentException('PDF URL is required.');
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE qc_certificates SET code=?, product_name=?, product_id=?, batch_number=?, issued_at=?, pdf_url=?, notes=? WHERE id=?");
        $stmt->execute([$code, $productName, $productId, $batchNumber, $issuedAt, $pdfUrl, $notes, (int)$id]);
        return (int)$id;
    }

    $stmt = $pdo->prepare("INSERT INTO qc_certificates (code, product_name, product_id, batch_number, issued_at, pdf_url, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$code, $productName, $productId, $batchNumber, $issuedAt, $pdfUrl, $notes]);
    return (int)$pdo->lastInsertId();
}

function deleteQcCertificate($id) {
    ensureQcCertificatesSchema();
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM qc_certificates WHERE id = ?");
    return $stmt->execute([(int)$id]);
}

/**
 * Parses a COA filename like:
 *   2026-05-14–r2qc-2026-24044-381-hgh-176-191-5mg.pdf
 * Returns: ['code' => 'R2QC-2026-24044-381', 'issued_at' => '2026-05-14', 'product_name' => 'HGH 176-191 5mg']
 * Returns null if pattern doesn't match (e.g. batch-combined file).
 */
function parseCoaFilename($filename) {
    $base = preg_replace('/\.pdf$/i', '', $filename);
    if (!preg_match('/^(\d{4}-\d{2}-\d{2})[\x{2013}\x{2014}\-](r2qc-\d{4}-\d{5}-\d{3})-?(.*)$/u', $base, $m)) {
        return null;
    }

    $issuedAt = $m[1];
    $code = strtoupper($m[2]);
    $slug = trim($m[3], '-');
    $productName = '';
    if ($slug !== '') {
        $productName = str_replace('-', ' ', $slug);
        $productName = preg_replace_callback('/\b([a-z])/u', function ($w) { return strtoupper($w[1]); }, $productName);
        $productName = preg_replace('/(\d)\s+Mg\b/u', '$1mg', $productName);
        $productName = preg_replace('/(\d)\s+Iu\b/u', '$1IU', $productName);
    }

    return [
        'code' => $code,
        'issued_at' => $issuedAt,
        'product_name' => $productName,
    ];
}

/**
 * Scans the COA directory and inserts any certificate that isn't in the DB yet.
 * Returns an array with import statistics.
 */
function importQcCertificatesFromDir($dirPath, $publicUrlPrefix = '/uploads/qc') {
    ensureQcCertificatesSchema();
    global $pdo;

    $stats = ['imported' => 0, 'skipped' => 0, 'errors' => []];

    if (!is_dir($dirPath)) {
        $stats['errors'][] = "Directory not found: $dirPath";
        return $stats;
    }

    $files = glob(rtrim($dirPath, '/') . '/*.pdf') ?: [];

    foreach ($files as $filePath) {
        $filename = basename($filePath);
        $parsed = parseCoaFilename($filename);
        if (!$parsed) {
            $stats['skipped']++;
            continue;
        }

        $existing = getQcCertificateByCode($parsed['code']);
        if ($existing) {
            $stats['skipped']++;
            continue;
        }

        try {
            saveQcCertificate([
                'code' => $parsed['code'],
                'product_name' => $parsed['product_name'],
                'issued_at' => $parsed['issued_at'],
                'pdf_url' => rtrim($publicUrlPrefix, '/') . '/' . $filename,
            ]);
            $stats['imported']++;
        } catch (Throwable $e) {
            $stats['errors'][] = "$filename: " . $e->getMessage();
        }
    }

    return $stats;
}
