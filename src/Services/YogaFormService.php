<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Repositories\YogaFormRepository;

final class YogaFormService
{
    private readonly RegistrationFileUploadService $fileUpload;

    public function __construct(
        private readonly YogaFormRepository $repository = new YogaFormRepository(),
        ?RegistrationFileUploadService $fileUpload = null
    ) {
        $this->fileUpload = $fileUpload ?? RegistrationFileUploadService::fromConfig();
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

        $mappedFiles = [
            'aadhaar_doc' => $files['aadhar_card_front'] ?? ['error' => UPLOAD_ERR_NO_FILE, 'tmp_name' => '', 'name' => '', 'type' => '', 'size' => 0],
            'aadhaar_doc_back' => $files['aadhar_card_back'] ?? ['error' => UPLOAD_ERR_NO_FILE, 'tmp_name' => '', 'name' => '', 'type' => '', 'size' => 0],
            'transaction_receipt_image' => $files['transaction_receipt_image'] ?? ['error' => UPLOAD_ERR_NO_FILE, 'tmp_name' => '', 'name' => '', 'type' => '', 'size' => 0],
        ];
        $docPaths = $this->fileUpload->processRegistrationDocs($mappedFiles);

        $submissionId = $this->repository->create([
            'author_name' => $payload['author_name'],
            'father_or_mother_name' => $payload['father_or_mother_name'],
            'course_name' => $payload['course_name'],
            'year_of_learning' => $payload['year_of_learning'] ?? null,
            'qualification' => $payload['qualification'],
            'previous_course' => $payload['previous_course'] ?? null,
            'sibling_details' => $payload['sibling_details'] ?? null,
            'age_or_birth_date' => $payload['age_or_birth_date'],
            'location' => $payload['location'],
            'mentor_name' => $payload['mentor_name'] ?? null,
            'mentor_occupation' => $payload['mentor_occupation'] ?? null,
            'mentor_phone' => $payload['mentor_phone'] ?? null,
            'referrer_name' => $payload['referrer_name'] ?? null,
            'referrer_phone' => $payload['referrer_phone'] ?? null,
            'referrer_occupation' => $payload['referrer_occupation'] ?? null,
            'another_referrer_name' => $payload['another_referrer_name'] ?? null,
            'another_referrer_phone' => $payload['another_referrer_phone'] ?? null,
            'another_referrer_occupation' => $payload['another_referrer_occupation'] ?? null,
            'amount_paid' => $amountPaid,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'additional_message' => $payload['additional_message'] ?? null,
            'mobile' => $payload['mobile'] ?? null,
            'aadhaar_front_path' => $docPaths['aadhaar_doc_path'],
            'aadhaar_back_path' => $docPaths['aadhaar_doc_back_path'],
            'transaction_receipt_path' => $docPaths['transaction_receipt_path'],
        ]);

        return [
            'id' => $submissionId,
            'author_name' => $payload['author_name'],
            'course_name' => $payload['course_name'],
            'amount_paid' => $amountPaid,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'mobile' => $payload['mobile'] ?? null,
        ];
    }

    public function listByMobile(string $mobile): array
    {
        return $this->repository->listByMobile($mobile);
    }
}
