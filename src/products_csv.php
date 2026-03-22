<?php

function getProductsCsvColumns() {
    return ['name', 'sku', 'slug', 'price', 'category', 'image_url', 'short_desc', 'long_desc', 'pdf_url', 'pdf_label'];
}

function normalizeProductsCsvHeader($value) {
    return strtolower(trim((string)$value));
}

function streamCsvFileResponse($filename, $headers, $rows) {
    if (!headers_sent()) {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($output, $headers, ',', '"', '\\');
    foreach ($rows as $row) {
        fputcsv($output, $row, ',', '"', '\\');
    }
    fclose($output);
}

function streamProductsCsvTemplate() {
    $headers = getProductsCsvColumns();
    $rows = [[
        'Example Product',
        'SKU-001',
        'example-product',
        '99.90',
        'Peptides',
        'https://example.com/product.png',
        '<p>Short description</p>',
        '<p>Long description</p>',
        'https://example.com/laudo.pdf',
        'Download Analysis Report'
    ]];
    streamCsvFileResponse('products-template.csv', $headers, $rows);
}

function exportProductsToCsv() {
    $products = getAllProducts();
    $headers = getProductsCsvColumns();
    $rows = [];
    foreach ($products as $product) {
        $rows[] = [
            $product['name'] ?? '',
            $product['sku'] ?? '',
            $product['slug'] ?? '',
            $product['price'] ?? '',
            $product['category_name'] ?? '',
            $product['image_url'] ?? '',
            $product['short_desc'] ?? '',
            $product['long_desc'] ?? '',
            $product['pdf_url'] ?? '',
            $product['pdf_label'] ?? ''
        ];
    }
    streamCsvFileResponse('products-export.csv', $headers, $rows);
}

function getOrCreateCategoryIdByName($name) {
    global $pdo;
    $normalized = trim((string)$name);
    if ($normalized === '') {
        return null;
    }

    $stmt = $pdo->prepare("SELECT id FROM categories WHERE lower(trim(name)) = lower(trim(?)) LIMIT 1");
    $stmt->execute([$normalized]);
    $existingId = $stmt->fetchColumn();
    if ($existingId) {
        return (int)$existingId;
    }

    $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $normalized)));
    $baseSlug = trim($baseSlug, '-');
    if ($baseSlug === '') {
        $baseSlug = 'category';
    }

    $slug = $baseSlug;
    $suffix = 2;
    while (true) {
        $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ? LIMIT 1");
        $check->execute([$slug]);
        if (!$check->fetchColumn()) {
            break;
        }
        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
    }

    $insert = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
    $insert->execute([$normalized, $slug]);
    return (int)$pdo->lastInsertId();
}

function parseCsvDecimalOrNull($rawValue, &$isValid) {
    $value = trim((string)$rawValue);
    if ($value === '') {
        $isValid = true;
        return null;
    }

    $normalized = str_replace(' ', '', $value);
    if (strpos($normalized, ',') !== false && strpos($normalized, '.') !== false) {
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);
    } elseif (strpos($normalized, ',') !== false) {
        $normalized = str_replace(',', '.', $normalized);
    }

    if (!is_numeric($normalized)) {
        $isValid = false;
        return null;
    }

    $isValid = true;
    return (float)$normalized;
}

function importProductsFromCsvUpload($uploadFile) {
    $result = [
        'total' => 0,
        'inserted' => 0,
        'updated' => 0,
        'ignored' => 0,
        'errors' => []
    ];

    if (!isset($uploadFile['error']) || (int)$uploadFile['error'] !== UPLOAD_ERR_OK || empty($uploadFile['tmp_name'])) {
        $result['errors'][] = 'Falha no upload do arquivo CSV.';
        return $result;
    }

    $csvName = (string)($uploadFile['name'] ?? '');
    $csvSize = (int)($uploadFile['size'] ?? 0);
    $csvExt = strtolower(pathinfo($csvName, PATHINFO_EXTENSION));
    if ($csvExt !== 'csv') {
        $result['errors'][] = 'Arquivo inválido: use extensão .csv.';
        return $result;
    }
    if ($csvSize <= 0 || $csvSize > (10 * 1024 * 1024)) {
        $result['errors'][] = 'Arquivo CSV fora do limite (máximo 10MB).';
        return $result;
    }
    if (!is_uploaded_file((string)$uploadFile['tmp_name'])) {
        $result['errors'][] = 'Upload CSV inválido.';
        return $result;
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = finfo_file($finfo, (string)$uploadFile['tmp_name']) ?: '';
            finfo_close($finfo);
            $allowedMimeTypes = ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel'];
            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                $result['errors'][] = 'Tipo MIME do CSV inválido.';
                return $result;
            }
        }
    }

    $handle = fopen($uploadFile['tmp_name'], 'r');
    if (!$handle) {
        $result['errors'][] = 'Não foi possível ler o arquivo CSV.';
        return $result;
    }

    $headers = fgetcsv($handle, 0, ',', '"', '\\');
    if (!$headers || count($headers) === 0) {
        fclose($handle);
        $result['errors'][] = 'CSV vazio ou sem cabeçalho.';
        return $result;
    }

    if (isset($headers[0])) {
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$headers[0]);
    }

    $columnIndexMap = [];
    foreach ($headers as $idx => $header) {
        $columnIndexMap[normalizeProductsCsvHeader($header)] = $idx;
    }

    $expectedColumns = getProductsCsvColumns();
    if (!isset($columnIndexMap['name'])) {
        fclose($handle);
        $result['errors'][] = 'Cabeçalho inválido: coluna "name" é obrigatória.';
        return $result;
    }

    $line = 1;
    while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
        $line++;
        $result['total']++;

        $rowData = [];
        foreach ($expectedColumns as $column) {
            $index = $columnIndexMap[$column] ?? null;
            $rowData[$column] = $index !== null ? trim((string)($row[$index] ?? '')) : '';
        }

        $hasAnyData = false;
        foreach ($rowData as $value) {
            if ($value !== '') {
                $hasAnyData = true;
                break;
            }
        }
        if (!$hasAnyData) {
            $result['ignored']++;
            continue;
        }

        if ($rowData['name'] === '') {
            $result['errors'][] = "Linha {$line}: campo name é obrigatório.";
            $result['ignored']++;
            continue;
        }

        $isPriceValid = true;
        $price = parseCsvDecimalOrNull($rowData['price'], $isPriceValid);
        if (!$isPriceValid) {
            $result['errors'][] = "Linha {$line}: preço inválido ({$rowData['price']}).";
            $result['ignored']++;
            continue;
        }

        try {
            $categoryId = getOrCreateCategoryIdByName($rowData['category']);
            $payload = [
                'name' => $rowData['name'],
                'sku' => $rowData['sku'],
                'slug' => $rowData['slug'] ?? '',
                'price' => $price,
                'image_url' => $rowData['image_url'],
                'category_id' => $categoryId,
                'short_desc' => $rowData['short_desc'],
                'long_desc' => $rowData['long_desc'],
                'pdf_url' => $rowData['pdf_url'],
                'pdf_label' => $rowData['pdf_label']
            ];

            $existing = null;
            if ($payload['sku'] !== '') {
                $existing = getProductBySku($payload['sku']);
            }

            if ($existing && isset($existing['id'])) {
                updateProduct($existing['id'], $payload);
                $result['updated']++;
            } else {
                createProduct($payload);
                $result['inserted']++;
            }
        } catch (Throwable $e) {
            $result['errors'][] = "Linha {$line}: erro ao salvar produto.";
            $result['ignored']++;
        }
    }

    fclose($handle);
    return $result;
}
