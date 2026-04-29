<?php
require 'src/products.php';
$start = microtime(true);
$data = [
    'name' => 'Test Product',
    'sku' => 'SKU-001',
    'price' => 10.00,
    'slug' => 'test-product'
];
updateProduct(1, $data);
$end = microtime(true);
echo "Time: " . ($end - $start) . " seconds\n";
