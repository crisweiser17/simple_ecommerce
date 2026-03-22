<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/products.php';
require_once __DIR__ . '/orders.php';
require_once __DIR__ . '/functions.php';

class DownloadHandler {
    public function processDownload($token) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT * FROM order_digital_deliveries WHERE token = ?");
        $stmt->execute([$token]);
        $delivery = $stmt->fetch();
        
        if (!$delivery) {
            http_response_code(404);
            echo __('Invalid or expired download link.');
            return;
        }
        
        if (!empty($delivery['expires_at'])) {
            $expiresAt = strtotime($delivery['expires_at']);
            if (time() > $expiresAt) {
                http_response_code(403);
                echo __('This download link has expired.');
                return;
            }
        }
        
        $maxDownloads = (int)$delivery['max_downloads'];
        $currentDownloads = (int)$delivery['download_count'];
        
        if ($maxDownloads > 0 && $currentDownloads >= $maxDownloads) {
            http_response_code(403);
            echo __('Download limit reached for this file.');
            return;
        }
        
        $product = getProduct($delivery['product_id']);
        if (!$product || empty($product['file_url'])) {
            http_response_code(404);
            echo __('File not found.');
            return;
        }
        
        $updateStmt = $pdo->prepare("
            UPDATE order_digital_deliveries 
            SET download_count = download_count + 1, downloaded_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $updateStmt->execute([$delivery['id']]);
        
        $fileUrl = $product['file_url'];
        
        if (strpos($fileUrl, 'http') === 0) {
            header("Location: $fileUrl");
            exit;
        } else {
            // Assume the file path is relative to the root directory
            $basePath = realpath(__DIR__ . '/../');
            
            // If the URL starts with /storage/digital, we map it directly
            $filePath = realpath($basePath . $fileUrl);
            
            // Fallback for public paths if the file is in public/uploads/
            if (!$filePath && strpos($fileUrl, '/uploads/') === 0) {
                $filePath = realpath($basePath . '/public' . $fileUrl);
            }
            
            if ($filePath && file_exists($filePath) && strpos($filePath, $basePath) === 0) {
                $mimeType = function_exists('mime_content_type') ? mime_content_type($filePath) : 'application/octet-stream';
                $fileName = basename($filePath);
                
                header('Content-Type: ' . $mimeType);
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                header('Content-Length: ' . filesize($filePath));
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                
                // Clear output buffer before sending the file
                if (ob_get_level()) {
                    ob_end_clean();
                }
                
                readfile($filePath);
                exit;
            } else {
                http_response_code(404);
                echo __('File not found on server.');
                return;
            }
        }
    }
}
