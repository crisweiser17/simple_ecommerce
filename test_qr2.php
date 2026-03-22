<?php
require 'vendor/autoload.php';
$qrOptions = new \chillerlan\QRCode\QROptions([
    'scale' => 5,
    'eccLevel' => \chillerlan\QRCode\Common\EccLevel::M,
    'addQuietzone' => true,
]);
$qrCodeUrl = (new \chillerlan\QRCode\QRCode($qrOptions))->render("TEST");
echo substr($qrCodeUrl, 0, 100) . "\n";
