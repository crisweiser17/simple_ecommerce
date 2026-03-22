<?php

class FileUploader {
    private string $uploadDir;
    private array $allowedExtensions;
    private array $allowedMimeTypes;
    private int $maxFileSize;

    public function __construct(
        string $uploadDir = __DIR__ . '/../storage/digital/',
        array $allowedExtensions = ['pdf', 'zip', 'mp4', 'docx'],
        array $allowedMimeTypes = [
            'application/pdf',
            'application/zip',
            'video/mp4',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ],
        int $maxFileSize = 26214400 // 25MB in bytes
    ) {
        $this->uploadDir = $uploadDir;
        $this->allowedExtensions = $allowedExtensions;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxFileSize = $maxFileSize;

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Handles file upload
     * @param array|null $file $_FILES['input_name']
     * @return string Returns the file path or URL on success, empty string on failure
     */
    public function upload(?array $file): string {
        if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return '';
        }

        if ($file['size'] > $this->maxFileSize) {
            return '';
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $this->allowedMimeTypes, true)) {
            return '';
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            return '';
        }

        $filename = uniqid('digital_', true) . '.' . $extension;
        $destination = rtrim($this->uploadDir, '/') . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Protect the directory if it's new
            $this->protectDirectory();
            return '/storage/digital/' . $filename;
        }

        return '';
    }

    private function protectDirectory(): void {
        $htaccessPath = rtrim($this->uploadDir, '/') . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            file_put_contents($htaccessPath, "Order Allow,Deny\nDeny from all\n");
        }
    }
}
