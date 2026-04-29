<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/products.php';

function assertTrue($condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assertSameValue($expected, $actual, string $message): void {
    if ($expected !== $actual) {
        throw new RuntimeException($message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true));
    }
}

$baseData = [
    'id' => '42',
    'name' => 'Produto de Teste',
    'pdf_url' => '/uploads/pdfs/existente.pdf',
    'file_url' => '/storage/digital/existente.zip',
];

$uploadCalls = [
    'pdf' => 0,
    'digital' => 0,
    'images' => 0,
];

$noUploadFiles = [
    'pdf_file' => [
        'name' => '',
        'tmp_name' => '',
        'error' => UPLOAD_ERR_NO_FILE,
        'size' => 0,
    ],
    'digital_file' => [
        'name' => '',
        'tmp_name' => '',
        'error' => UPLOAD_ERR_NO_FILE,
        'size' => 0,
    ],
    'product_images' => [
        'name' => [''],
        'tmp_name' => [''],
        'error' => [UPLOAD_ERR_NO_FILE],
        'size' => [0],
    ],
];

$resultWithoutUploads = prepareProductAssetPayload(
    $baseData,
    $noUploadFiles,
    function () use (&$uploadCalls) {
        $uploadCalls['pdf']++;
        return '/uploads/pdfs/novo.pdf';
    },
    function () use (&$uploadCalls) {
        $uploadCalls['digital']++;
        return '/storage/digital/novo.zip';
    },
    function () use (&$uploadCalls) {
        $uploadCalls['images']++;
        return ['/uploads/products/nova-1.png'];
    }
);

assertSameValue(0, $uploadCalls['pdf'], 'Nao deve chamar upload de PDF quando nenhum arquivo foi enviado.');
assertSameValue(0, $uploadCalls['digital'], 'Nao deve chamar upload de arquivo digital quando nenhum arquivo foi enviado.');
assertSameValue(0, $uploadCalls['images'], 'Nao deve chamar upload de galeria quando nenhum arquivo foi enviado.');
assertSameValue($baseData['pdf_url'], $resultWithoutUploads['pdf_url'], 'Deve preservar pdf_url existente ao salvar apenas metadados.');
assertSameValue($baseData['file_url'], $resultWithoutUploads['file_url'], 'Deve preservar file_url existente ao salvar apenas metadados.');
assertSameValue([], $resultWithoutUploads['new_uploaded_images'], 'Nao deve registrar novas imagens quando nenhum upload ocorreu.');

$uploadCalls = [
    'pdf' => 0,
    'digital' => 0,
    'images' => 0,
];

$newUploadFiles = [
    'pdf_file' => [
        'name' => 'novo.pdf',
        'tmp_name' => '/tmp/php-pdf',
        'error' => UPLOAD_ERR_OK,
        'size' => 123,
    ],
    'digital_file' => [
        'name' => 'novo.zip',
        'tmp_name' => '/tmp/php-digital',
        'error' => UPLOAD_ERR_OK,
        'size' => 456,
    ],
    'product_images' => [
        'name' => ['foto-1.png', 'foto-2.png'],
        'tmp_name' => ['/tmp/php-img-1', '/tmp/php-img-2'],
        'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
        'size' => [111, 222],
    ],
];

$resultWithUploads = prepareProductAssetPayload(
    $baseData,
    $newUploadFiles,
    function (array $file) use (&$uploadCalls) {
        $uploadCalls['pdf']++;
        assertSameValue('novo.pdf', $file['name'], 'Deve encaminhar o PDF enviado para o callback.');
        return '/uploads/pdfs/novo.pdf';
    },
    function (array $file) use (&$uploadCalls) {
        $uploadCalls['digital']++;
        assertSameValue('novo.zip', $file['name'], 'Deve encaminhar o arquivo digital enviado para o callback.');
        return '/storage/digital/novo.zip';
    },
    function (array $files) use (&$uploadCalls) {
        $uploadCalls['images']++;
        assertSameValue('foto-1.png', $files['name'][0], 'Deve encaminhar apenas o lote real de imagens enviadas.');
        return [
            '/uploads/products/nova-1.png',
            '/uploads/products/nova-2.png',
            '/uploads/products/nova-1.png',
        ];
    }
);

assertSameValue(1, $uploadCalls['pdf'], 'Deve chamar o upload de PDF quando houver arquivo novo.');
assertSameValue(1, $uploadCalls['digital'], 'Deve chamar o upload de arquivo digital quando houver arquivo novo.');
assertSameValue(1, $uploadCalls['images'], 'Deve chamar o upload de imagens quando houver novos arquivos.');
assertSameValue('/uploads/pdfs/novo.pdf', $resultWithUploads['pdf_url'], 'Deve substituir pdf_url pelo upload novo.');
assertSameValue('/storage/digital/novo.zip', $resultWithUploads['file_url'], 'Deve substituir file_url pelo upload novo.');
assertSameValue(
    ['/uploads/products/nova-1.png', '/uploads/products/nova-2.png'],
    $resultWithUploads['new_uploaded_images'],
    'Deve manter apenas imagens novas unicas retornadas pelo callback.'
);

assertTrue(hasSuccessfulUpload($newUploadFiles['pdf_file']), 'Upload unico valido deve ser detectado.');
assertTrue(hasSuccessfulUploads($newUploadFiles['product_images']), 'Lote com uploads validos deve ser detectado.');
assertTrue(!hasSuccessfulUpload($noUploadFiles['pdf_file']), 'Campo vazio nao deve ser tratado como upload valido.');
assertTrue(!hasSuccessfulUploads($noUploadFiles['product_images']), 'Lote vazio nao deve ser tratado como upload valido.');

echo "OK\n";
