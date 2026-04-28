<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Repositories\HealingFormRepository;
use RuntimeException;

final class HealingFormService
{
    private readonly string $baseDir;
    private readonly int $maxSizeBytes;
    /** @var array<int, string> */
    private readonly array $allowedMimes;

    public function __construct(
        private readonly HealingFormRepository $repository = new HealingFormRepository()
    ) {
        $config = require __DIR__ . '/../../config/config.php';
        $uploadConfig = $config['uploads'] ?? [];
        $this->baseDir = (string) ($uploadConfig['registrations_dir'] ?? (__DIR__ . '/../../storage/uploads/registrations'));
        $this->maxSizeBytes = (int) ($uploadConfig['max_size_bytes'] ?? 5 * 1024 * 1024);
        $this->allowedMimes = (array) ($uploadConfig['allowed_mimes'] ?? ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, array{name: string, type: string, tmp_name: string, error: int, size: int}> $files
     * @return array<string, mixed>
     */
    public function submit(array $payload, array $files): array
    {
        $amountPaid = (float) $payload['amount_paid'];
        if ($amountPaid <= 0) {
            throw new HttpException('Amount paid must be greater than zero.', 422);
        }

        $aadhaarFrontPath = $this->storeFile($files, 'aadhar_card_front', true);
        $aadhaarBackPath = $this->storeFile($files, 'aadhar_card_back', true);
        $receiptPath = $this->storeFile($files, 'transaction_receipt_image', true);
        $currentPicturePath = $this->storeFile($files, 'current_picture', false);

        $id = $this->repository->create([
            'full_name' => $payload['full_name'],
            'date_of_birth' => $payload['date_of_birth'],
            'time_of_birth' => $payload['time_of_birth'] ?? null,
            'place_of_birth' => $payload['place_of_birth'] ?? null,
            'current_location' => $payload['current_location'] ?? null,
            'mobile' => $payload['mobile'],
            'email' => $payload['email'] ?? null,
            'address' => $payload['address'] ?? null,
            'aadhaar_number' => $payload['aadhaar_number'],
            'aadhaar_front_path' => $aadhaarFrontPath,
            'aadhaar_back_path' => $aadhaarBackPath,
            'issue_type' => $payload['issue_type'] ?? null,
            'issue_description' => $payload['issue_description'] ?? null,
            'current_picture_path' => $currentPicturePath,
            'declaration_accepted' => $payload['declaration_accepted'],
            'amount_paid' => $amountPaid,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'transaction_receipt_path' => $receiptPath,
        ]);

        return [
            'id' => $id,
            'full_name' => $payload['full_name'],
            'mobile' => $payload['mobile'],
            'amount_paid' => $amountPaid,
        ];
    }

    public function listByMobile(string $mobile): array
    {
        return $this->repository->listByMobile($mobile);
    }

    /**
     * @param array<string, array{name: string, type: string, tmp_name: string, error: int, size: int}> $files
     */
    private function storeFile(array $files, string $key, bool $required): ?string
    {
        $file = $files[$key] ?? null;
        if ($file === null || $file['error'] === UPLOAD_ERR_NO_FILE || $file['tmp_name'] === '') {
            if ($required) {
                throw new HttpException('Missing required file: ' . $key, 422);
            }
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new HttpException('Upload failed for ' . $key, 422);
        }
        if ($file['size'] > $this->maxSizeBytes) {
            throw new HttpException('File too large for ' . $key, 422);
        }

        $mime = $this->detectMime($file['tmp_name'], $file['type']);
        if (!in_array($mime, $this->allowedMimes, true)) {
            throw new HttpException('Invalid file type for ' . $key . '. Allowed: JPEG, PNG, WebP, PDF.', 422);
        }

        if (!is_dir($this->baseDir) && !mkdir($concurrentDirectory = $this->baseDir, 0755, true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException('Could not create uploads directory.');
        }

        $ext = $this->mimeToExt($mime);
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $safeName = substr($safeName, 0, 100) ?: 'file';
        $filename = date('Y-m-d_His') . '_' . $key . '_' . $safeName;
        if (strlen(pathinfo($filename, PATHINFO_EXTENSION)) < 2) {
            $filename .= $ext;
        }
        $destPath = $this->baseDir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new RuntimeException('Failed to save uploaded file: ' . $key);
        }

        return 'registrations/' . $filename;
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
