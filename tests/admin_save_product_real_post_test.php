<?php

declare(strict_types=1);

function assertTrue($condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message
            . "\nExpected: " . var_export($expected, true)
            . "\nActual: " . var_export($actual, true)
        );
    }
}

function assertStartsWith(string $prefix, string $value, string $message): void
{
    if (strpos($value, $prefix) !== 0) {
        throw new RuntimeException($message . "\nExpected prefix: {$prefix}\nActual: {$value}");
    }
}

function removeDirectoryRecursive(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        unlink($path);
        return;
    }

    $items = scandir($path);
    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        removeDirectoryRecursive($path . DIRECTORY_SEPARATOR . $item);
    }

    rmdir($path);
}

function createPngFixture(string $path): void
{
    $pngData = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO7Z0XQAAAAASUVORK5CYII=',
        true
    );
    if ($pngData === false) {
        throw new RuntimeException('Falha ao gerar fixture PNG.');
    }

    file_put_contents($path, $pngData);
}

function createZipFixture(string $path): void
{
    $zip = new ZipArchive();
    $result = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    if ($result !== true) {
        throw new RuntimeException('Falha ao gerar fixture ZIP real.');
    }

    $zip->addFromString('readme.txt', "fixture zip\n");
    $zip->close();
}

function runCommand(string $command, string $errorMessage): string
{
    $output = [];
    $exitCode = 0;
    exec($command . ' 2>&1', $output, $exitCode);
    $combinedOutput = implode("\n", $output);

    if ($exitCode !== 0) {
        throw new RuntimeException($errorMessage . "\nCommand: {$command}\nOutput:\n{$combinedOutput}");
    }

    return $combinedOutput;
}

function runCurl(array $arguments, string $errorMessage): string
{
    $command = 'curl -sS --max-time 15';
    foreach ($arguments as $argument) {
        $command .= ' ' . $argument;
    }

    return runCommand($command, $errorMessage);
}

function waitForHttpServer(string $url, int $attempts = 50, int $delayMicros = 100000): void
{
    for ($attempt = 0; $attempt < $attempts; $attempt++) {
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 1,
            ],
        ]);

        $result = @file_get_contents($url, false, $context);
        if ($result !== false || !empty($http_response_header)) {
            return;
        }

        usleep($delayMicros);
    }

    throw new RuntimeException('Servidor HTTP de teste nao respondeu a tempo.');
}

$projectRoot = dirname(__DIR__);
$tempRoot = sys_get_temp_dir() . '/r2-admin-save-product-' . bin2hex(random_bytes(6));
$dbFile = $tempRoot . '/data/database.sqlite';
$sessionPath = $tempRoot . '/sessions';
$publicUploadsDir = $tempRoot . '/public_uploads';
$digitalStorageDir = $tempRoot . '/storage/digital';
$cookieJar = $tempRoot . '/cookies.txt';
$serverLog = $tempRoot . '/server.log';
$productUploadsDir = $publicUploadsDir . '/products';
$pdfUploadsDir = $publicUploadsDir . '/pdfs';
$fixturesDir = $tempRoot . '/fixtures';
$existingPrimaryImage = '/uploads/products/existente-1.png';
$existingGalleryImage = '/uploads/products/existente-2.png';
$existingPdfUrl = '/uploads/pdfs/existente.pdf';
$existingDigitalUrl = '/storage/digital/existente.zip';
$serverProcess = null;

mkdir($tempRoot, 0777, true);
mkdir(dirname($dbFile), 0777, true);
mkdir($sessionPath, 0777, true);
mkdir($productUploadsDir, 0777, true);
mkdir($pdfUploadsDir, 0777, true);
mkdir($digitalStorageDir, 0777, true);
mkdir($fixturesDir, 0777, true);

file_put_contents($pdfUploadsDir . '/existente.pdf', "%PDF-1.4\n% fixture\n");
file_put_contents($digitalStorageDir . '/existente.zip', 'existing digital fixture');
createPngFixture($productUploadsDir . '/existente-1.png');
createPngFixture($productUploadsDir . '/existente-2.png');

putenv('R2_DB_FILE=' . $dbFile);
putenv('R2_SESSION_PATH=' . $sessionPath);
putenv('R2_PUBLIC_UPLOADS_DIR=' . $publicUploadsDir);
putenv('R2_STORAGE_DIGITAL_DIR=' . $digitalStorageDir);

require_once $projectRoot . '/src/db.php';
require_once $projectRoot . '/src/categories.php';
require_once $projectRoot . '/src/products.php';
require_once $projectRoot . '/src/user.php';

ensureUsersSchema();
ensureCategoriesSchema();
ensureProductsSchema();

createCategory(['name' => 'Categoria de Teste']);
global $pdo;
$categoryId = (int)$pdo->lastInsertId();

$productId = createProduct([
    'name' => 'Produto Original',
    'slug' => 'produto-original',
    'sku' => 'SKU-REAL-1',
    'price' => '10.50',
    'category_ids' => [$categoryId],
    'short_desc' => 'Descricao curta original',
    'long_desc' => 'Descricao longa original',
    'pdf_url' => $existingPdfUrl,
    'file_url' => $existingDigitalUrl,
    'variations_json' => '[]',
    'image_url' => $existingPrimaryImage,
]);
saveProductImages($productId, [$existingPrimaryImage, $existingGalleryImage], $existingPrimaryImage);
updateProductImageUrl($productId, $existingPrimaryImage);

$port = random_int(18080, 18999);
$serverCommand = sprintf(
    'R2_DB_FILE=%s R2_SESSION_PATH=%s R2_PUBLIC_UPLOADS_DIR=%s R2_STORAGE_DIGITAL_DIR=%s %s -S 127.0.0.1:%d index.php',
    escapeshellarg($dbFile),
    escapeshellarg($sessionPath),
    escapeshellarg($publicUploadsDir),
    escapeshellarg($digitalStorageDir),
    escapeshellarg(PHP_BINARY),
    $port
);

$descriptorSpec = [
    0 => ['pipe', 'r'],
    1 => ['file', $serverLog, 'a'],
    2 => ['file', $serverLog, 'a'],
];

try {
    $serverProcess = proc_open($serverCommand, $descriptorSpec, $pipes, $projectRoot);
    if (!is_resource($serverProcess)) {
        throw new RuntimeException('Falha ao iniciar o servidor HTTP de teste.');
    }

    fclose($pipes[0]);
    waitForHttpServer("http://127.0.0.1:{$port}/login");

    $loginResponse = runCurl([
        '-c ' . escapeshellarg($cookieJar),
        '-b ' . escapeshellarg($cookieJar),
        '-H ' . escapeshellarg('Content-Type: application/json'),
        '-d ' . escapeshellarg(json_encode([
            'email' => 'admin@r2.com',
            'token' => '000000',
        ])),
        escapeshellarg("http://127.0.0.1:{$port}/api/login-verify.php"),
    ], 'Falha ao autenticar sessao admin.');

    $loginData = json_decode($loginResponse, true);
    assertTrue(is_array($loginData) && !empty($loginData['success']), 'Login admin via sessao deve funcionar no teste real.');

    $initialProductFiles = glob($productUploadsDir . '/*') ?: [];
    $initialPdfFiles = glob($pdfUploadsDir . '/*') ?: [];
    $initialDigitalFiles = glob($digitalStorageDir . '/*') ?: [];

    $preserveResponse = runCurl([
        '-c ' . escapeshellarg($cookieJar),
        '-b ' . escapeshellarg($cookieJar),
        '-H ' . escapeshellarg('Accept: application/json'),
        '-F ' . escapeshellarg('id=' . $productId),
        '-F ' . escapeshellarg('name=Produto Atualizado Sem Upload'),
        '-F ' . escapeshellarg('slug=produto-atualizado-sem-upload'),
        '-F ' . escapeshellarg('sku=SKU-REAL-1'),
        '-F ' . escapeshellarg('price=99.90'),
        '-F ' . escapeshellarg('short_desc=Descricao curta sem upload'),
        '-F ' . escapeshellarg('long_desc=Descricao longa sem upload'),
        '-F ' . escapeshellarg('pdf_url=' . $existingPdfUrl),
        '-F ' . escapeshellarg('file_url=' . $existingDigitalUrl),
        '-F ' . escapeshellarg('variations_json=[]'),
        '-F ' . escapeshellarg('category_ids[]=' . $categoryId),
        '-F ' . escapeshellarg('existing_images[]=' . $existingPrimaryImage),
        '-F ' . escapeshellarg('existing_images[]=' . $existingGalleryImage),
        escapeshellarg("http://127.0.0.1:{$port}/admin/save-product"),
    ], 'Falha ao executar POST multipart sem novos arquivos.');

    $preserveData = json_decode($preserveResponse, true);
    assertTrue(is_array($preserveData) && !empty($preserveData['success']), 'POST real sem uploads deve responder sucesso.');

    $productAfterPreserve = getProduct($productId);
    assertTrue(is_array($productAfterPreserve), 'Produto atualizado deve continuar existente.');
    assertSameValue($existingPdfUrl, $productAfterPreserve['pdf_url'], 'Edicao sem novos arquivos deve preservar pdf_url.');
    assertSameValue($existingDigitalUrl, $productAfterPreserve['file_url'], 'Edicao sem novos arquivos deve preservar file_url.');
    assertSameValue(
        [$existingPrimaryImage, $existingGalleryImage],
        array_column($productAfterPreserve['images'], 'image_url'),
        'Edicao sem novos arquivos deve preservar a galeria existente.'
    );
    assertSameValue($initialProductFiles, glob($productUploadsDir . '/*') ?: [], 'Edicao sem upload nao deve criar imagens novas.');
    assertSameValue($initialPdfFiles, glob($pdfUploadsDir . '/*') ?: [], 'Edicao sem upload nao deve criar PDFs novos.');
    assertSameValue($initialDigitalFiles, glob($digitalStorageDir . '/*') ?: [], 'Edicao sem upload nao deve criar arquivos digitais novos.');

    $newPdfFixture = $fixturesDir . '/novo.pdf';
    $newDigitalFixture = $fixturesDir . '/novo.zip';
    $newImageFixture = $fixturesDir . '/nova.png';
    file_put_contents($newPdfFixture, "%PDF-1.4\n% novo fixture\n");
    createZipFixture($newDigitalFixture);
    createPngFixture($newImageFixture);

    $uploadResponse = runCurl([
        '-c ' . escapeshellarg($cookieJar),
        '-b ' . escapeshellarg($cookieJar),
        '-H ' . escapeshellarg('Accept: application/json'),
        '-F ' . escapeshellarg('id=' . $productId),
        '-F ' . escapeshellarg('name=Produto Atualizado Com Upload'),
        '-F ' . escapeshellarg('slug=produto-atualizado-com-upload'),
        '-F ' . escapeshellarg('sku=SKU-REAL-1'),
        '-F ' . escapeshellarg('price=149.90'),
        '-F ' . escapeshellarg('short_desc=Descricao curta com upload'),
        '-F ' . escapeshellarg('long_desc=Descricao longa com upload'),
        '-F ' . escapeshellarg('pdf_url=' . $existingPdfUrl),
        '-F ' . escapeshellarg('file_url=' . $existingDigitalUrl),
        '-F ' . escapeshellarg('variations_json=[]'),
        '-F ' . escapeshellarg('category_ids[]=' . $categoryId),
        '-F ' . escapeshellarg('existing_images[]=' . $existingPrimaryImage),
        '-F ' . escapeshellarg('existing_images[]=' . $existingGalleryImage),
        '-F ' . escapeshellarg('pdf_file=@' . $newPdfFixture . ';type=application/pdf'),
        '-F ' . escapeshellarg('digital_file=@' . $newDigitalFixture . ';type=application/zip'),
        '-F ' . escapeshellarg('product_images[]=@' . $newImageFixture . ';type=image/png'),
        escapeshellarg("http://127.0.0.1:{$port}/admin/save-product"),
    ], 'Falha ao executar POST multipart com novos arquivos.');

    $uploadData = json_decode($uploadResponse, true);
    assertTrue(
        is_array($uploadData) && !empty($uploadData['success']),
        "POST real com uploads deve responder sucesso.\nResposta bruta:\n{$uploadResponse}\nLog do servidor:\n"
        . (is_file($serverLog) ? (string)file_get_contents($serverLog) : '[sem log]')
    );

    $productAfterUpload = getProduct($productId);
    assertTrue(is_array($productAfterUpload), 'Produto com upload deve continuar existente.');
    assertStartsWith('/uploads/pdfs/pdf_', (string)$productAfterUpload['pdf_url'], 'Upload real deve gerar um novo pdf_url.');
    assertStartsWith('/storage/digital/digital_', (string)$productAfterUpload['file_url'], 'Upload real deve gerar um novo file_url.');
    assertTrue($productAfterUpload['pdf_url'] !== $existingPdfUrl, 'Upload real deve substituir o PDF antigo.');
    assertTrue($productAfterUpload['file_url'] !== $existingDigitalUrl, 'Upload real deve substituir o arquivo digital antigo.');

    $galleryUrls = array_column($productAfterUpload['images'], 'image_url');
    assertSameValue(3, count($galleryUrls), 'Upload real deve manter a galeria existente e adicionar apenas a nova imagem.');
    assertTrue(in_array($existingPrimaryImage, $galleryUrls, true), 'A imagem primaria existente deve permanecer na galeria.');
    assertTrue(in_array($existingGalleryImage, $galleryUrls, true), 'A imagem secundaria existente deve permanecer na galeria.');

    $newGalleryUrls = array_values(array_filter(
        $galleryUrls,
        static fn(string $url): bool => strpos($url, '/uploads/products/product_') === 0
    ));
    assertSameValue(1, count($newGalleryUrls), 'A requisicao real deve processar somente a nova imagem enviada.');

    $productFilesAfterUpload = glob($productUploadsDir . '/*') ?: [];
    $pdfFilesAfterUpload = glob($pdfUploadsDir . '/*') ?: [];
    $digitalFilesAfterUpload = glob($digitalStorageDir . '/*') ?: [];
    assertSameValue(count($initialProductFiles) + 1, count($productFilesAfterUpload), 'Deve existir apenas uma nova imagem fisica apos o upload.');
    assertSameValue(count($initialPdfFiles) + 1, count($pdfFilesAfterUpload), 'Deve existir apenas um novo PDF fisico apos o upload.');
    assertSameValue(count($initialDigitalFiles) + 1, count($digitalFilesAfterUpload), 'Deve existir apenas um novo arquivo digital fisico apos o upload.');

    echo "OK\n";
} finally {
    if (is_resource($serverProcess)) {
        proc_terminate($serverProcess);
        proc_close($serverProcess);
    }

    removeDirectoryRecursive($tempRoot);
}
