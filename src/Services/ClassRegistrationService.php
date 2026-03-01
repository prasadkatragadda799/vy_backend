<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Repositories\ClassPaymentRepository;
use App\Repositories\ClassRepository;

final class ClassRegistrationService
{
    private readonly RegistrationFileUploadService $fileUpload;

    public function __construct(
        private readonly ClassRepository $classRepository = new ClassRepository(),
        private readonly ClassPaymentRepository $paymentRepository = new ClassPaymentRepository(),
        ?RegistrationFileUploadService $fileUpload = null
    ) {
        $this->fileUpload = $fileUpload ?? RegistrationFileUploadService::fromConfig();
    }

    public function listClasses(): array
    {
        return $this->classRepository->allActive();
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, array{name: string, type: string, tmp_name: string, error: int, size: int}> $files
     */
    public function registerPayment(array $payload, array $files = []): array
    {
        $classId = (int) $payload['class_id'];
        $amount = (float) $payload['amount_paid'];

        if ($amount <= 0) {
            throw new HttpException('Amount must be greater than zero.', 422);
        }

        $class = $this->classRepository->findById($classId);
        if ($class === null) {
            throw new HttpException('Selected class is invalid.', 404);
        }

        $aadhaarNumber = (string) ($payload['aadhaar_number'] ?? '');
        if ($aadhaarNumber === '') {
            throw new HttpException('Aadhaar number is required.', 422);
        }
        $alreadyPaid = $this->paymentRepository->totalPaidByAadhaarAndClass($aadhaarNumber, $classId);
        $totalFee = (float) $class['total_fee'];
        $remainingBefore = max($totalFee - $alreadyPaid, 0);

        if ($remainingBefore <= 0) {
            throw new HttpException('Class fee is already fully paid for this Aadhaar number.', 409);
        }

        if ($amount > $remainingBefore) {
            throw new HttpException('Amount exceeds remaining fee. Remaining: ' . $remainingBefore, 422);
        }

        $remainingAfter = $remainingBefore - $amount;
        $status = $remainingAfter > 0 ? 'partial' : 'paid';

        $docPaths = ['aadhaar_doc_path' => null, 'aadhaar_doc_back_path' => null, 'transaction_receipt_path' => null];
        if (isset($files['aadhaar_doc'], $files['aadhaar_doc_back'], $files['transaction_receipt_image'])) {
            $docPaths = $this->fileUpload->processRegistrationDocs($files);
        }

        $paymentId = $this->paymentRepository->create([
            'mobile' => $payload['mobile'],
            'aadhaar_number' => $aadhaarNumber,
            'name' => $payload['name'],
            'email' => $payload['email'] ?? null,
            'class_id' => $classId,
            'preferred_time' => $payload['preferred_time'] ?? null,
            'location' => $payload['location'] ?? null,
            'siblings_name' => $payload['siblings_name'] ?? null,
            'message' => $payload['message'] ?? null,
            'amount_paid' => $amount,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'transaction_msg' => $payload['transaction_msg'] ?? null,
            'aadhaar_doc_path' => $docPaths['aadhaar_doc_path'],
            'aadhaar_doc_back_path' => $docPaths['aadhaar_doc_back_path'],
            'transaction_receipt_path' => $docPaths['transaction_receipt_path'],
            'payment_status' => $status,
        ]);

        return [
            'payment_id' => $paymentId,
            'class_id' => $classId,
            'class_name' => $class['class_name'],
            'total_fee' => $totalFee,
            'amount_paid_now' => $amount,
            'paid_till_now' => $alreadyPaid + $amount,
            'remaining_amount' => $remainingAfter,
            'payment_status' => $status,
        ];
    }

    public function mobileSummary(string $mobile): array
    {
        $rows = $this->paymentRepository->summaryByMobile($mobile);
        foreach ($rows as &$row) {
            $row['payment_status'] = ((float) $row['remaining_amount'] <= 0) ? 'paid' : 'partial_or_unpaid';
        }

        return $rows;
    }
}
