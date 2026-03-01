<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use RuntimeException;

final class RegistrationFileUploadService
{
    private const UPLOAD_KEYS = [
        'aadhaar_doc' => 'aadhaar_doc_path',
        'aadhaar_doc_back' => 'aadhaar_doc_back_path',
        'transaction_receipt_image' => 'transaction_receipt_path',
    ];

    public function __construct(
        private readonly string $registrationsDir,
        private readonly int $maxSizeBytes,
        private readonly array $allowedMimes
    ) {
    }

    public static function fromConfig(): self
    {
        $config = require __DIR__ . '/../../config/config.php';
        $uploadConfig = $config['uploads'] ?? [];
        return new self(
            (string) ($uploadConfig['registrations_dir'] ?? (__DIR__ . '/../../storage/uploads/registrations')),
            (int) ($uploadConfig['max_size_bytes'] ?? 5 * 1024 * 1024),
            (array) ($uploadConfig['allowed_mimes'] ?? ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
        );
    }

    /**
     * Validate and store registration docs (Aadhaar + transaction receipt).
     * @param array<string, array{name: string, type: string, tmp_name: string, error: int, size: int}> $files
     * @return array{aadhaar_doc_path: string|null, aadhaar_doc_back_path: string|null, transaction_receipt_path: string|null}
     */
    public function processRegistrationDocs(array $files): array
    {
        $baseDir = $this->registrationsDir;
        if (!is_dir($baseDir) && !mkdir($baseDir, 0755, true) && !is_dir($baseDir)) {
            throw new RuntimeException('Could not create uploads directory.');
        }

        $result = [
            'aadhaar_doc_path' => null,
            'aadhaar_doc_back_path' => null,
            'transaction_receipt_path' => null,
        ];

        foreach (self::UPLOAD_KEYS as $formKey => $pathKey) {
            $file = $files[$formKey] ?? null;
            if ($file === null || $file['error'] === UPLOAD_ERR_NO_FILE || $file['tmp_name'] === '') {
                throw new HttpException('Missing required document: ' . $formKey, 422);
            }
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new HttpException('Upload failed for ' . $formKey . ': ' . $this->uploadErrorMessage($file['error']), 422);
            }
            if ($file['size'] > $this->maxSizeBytes) {
                throw new HttpException('File too large for ' . $formKey . '. Max ' . round($this->maxSizeBytes / 1024 / 1024, 1) . ' MB.', 422);
            }

            $mime = $this->detectMime($file['tmp_name'], $file['type']);
            if (!in_array($mime, $this->allowedMimes, true)) {
                throw new HttpException('Invalid file type for ' . $formKey . '. Allowed: JPEG, PNG, WebP, PDF.', 422);
            }

            $ext = $this->mimeToExt($mime);
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
            $safeName = substr($safeName, 0, 100) ?: 'file';
            $filename = date('Y-m-d_His') . '_' . $formKey . '_' . $safeName;
            if (strlen(pathinfo($filename, PATHINFO_EXTENSION)) < 2) {
                $filename .= $ext;
            }
            $destPath = $baseDir . DIRECTORY_SEPARATOR . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                throw new RuntimeException('Failed to save uploaded file: ' . $formKey);
            }

            $result[$pathKey] = 'registrations/' . $filename;
        }

        return $result;
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File too large',
            UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by extension',
            default => 'Unknown upload error',
        };
    }

    private function detectMime(string $path, string $reportedType): string
    {
        if (is_file($path) && function_exists('mime_content_type')) {
            $detected = @mime_content_type($path);
            if (is_string($detected) && $detected !== '') {
                return $detected;
            }
        }
        return $reportedType;
    }

    private function mimeToExt(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/webp' => '.webp',
            'application/pdf' => '.pdf',
            default => '.bin',
        };
    }
}
