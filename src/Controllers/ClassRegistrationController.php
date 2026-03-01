<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\ClassRegistrationService;
use App\Validation\Validator;

final class ClassRegistrationController
{
    public function __construct(
        private readonly ClassRegistrationService $service = new ClassRegistrationService()
    ) {
    }

    public function listClasses(): void
    {
        Response::json([
            'success' => true,
            'data' => $this->service->listClasses(),
        ]);
    }

    public function registerPayment(Request $request): void
    {
        $validated = Validator::validate($request->body, [
            'name' => 'required',
            'mobile' => 'required|mobile',
            'class_id' => 'required|numeric',
            'amount_paid' => 'required|numeric',
            'email' => 'email_optional',
        ]);

        $validated['preferred_time'] = isset($request->body['preferred_time']) ? trim((string) $request->body['preferred_time']) : null;
        $validated['location'] = isset($request->body['location']) ? trim((string) $request->body['location']) : null;
        $validated['siblings_name'] = isset($request->body['siblings_name']) ? trim((string) $request->body['siblings_name']) : null;
        $validated['transaction_msg'] = isset($request->body['transaction_msg']) ? trim((string) $request->body['transaction_msg']) : null;
        $validated['transaction_id'] = isset($request->body['transaction_id']) ? trim((string) $request->body['transaction_id']) : null;
        $validated['message'] = isset($request->body['message']) ? trim((string) $request->body['message']) : null;
        if ($validated['message'] === '') {
            $validated['message'] = null;
        }

        $files = $request->files;
        $aadhaarFront = $files['aadhaar_doc'] ?? null;
        $aadhaarBack = $files['aadhaar_doc_back'] ?? null;
        $transactionReceipt = $files['transaction_receipt_image'] ?? null;
        if (!$aadhaarFront || $aadhaarFront['error'] === UPLOAD_ERR_NO_FILE || $aadhaarFront['tmp_name'] === '') {
            throw new HttpException('Aadhaar document (front) is required.', 422);
        }
        if (!$aadhaarBack || $aadhaarBack['error'] === UPLOAD_ERR_NO_FILE || $aadhaarBack['tmp_name'] === '') {
            throw new HttpException('Aadhaar document (back) is required.', 422);
        }
        if (!$transactionReceipt || $transactionReceipt['error'] === UPLOAD_ERR_NO_FILE || $transactionReceipt['tmp_name'] === '') {
            throw new HttpException('Transaction receipt image is required.', 422);
        }

        $result = $this->service->registerPayment($validated, $files);

        Response::json([
            'success' => true,
            'message' => 'Registration and payment recorded successfully.',
            'data' => $result,
        ], 201);
    }

    public function paymentSummary(Request $request): void
    {
        $mobile = trim((string) ($request->query['mobile'] ?? ''));
        if ($mobile === '') {
            throw new HttpException('Query param "mobile" is required.', 422);
        }

        Response::json([
            'success' => true,
            'data' => $this->service->mobileSummary($mobile),
        ]);
    }
}
