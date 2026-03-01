<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\DonationService;
use App\Validation\Validator;

final class DonationController
{
    public function __construct(
        private readonly DonationService $service = new DonationService()
    ) {
    }

    public function store(Request $request): void
    {
        $validated = Validator::validate($request->body, [
            'name' => 'required',
            'mobile' => 'required|mobile',
            'aadhaar_number' => 'required|aadhaar',
            'amount_paid' => 'required|numeric',
        ]);
        $validated['transaction_id'] = isset($request->body['transaction_id']) ? trim((string) $request->body['transaction_id']) : null;
        if ($validated['transaction_id'] === '') {
            $validated['transaction_id'] = null;
        }

        $result = $this->service->store($validated, $request->files);

        Response::json([
            'success' => true,
            'message' => 'Donation saved successfully.',
            'data' => $result,
        ], 201);
    }

    public function listByMobile(Request $request): void
    {
        $mobile = trim((string) ($request->query['mobile'] ?? ''));
        if ($mobile === '') {
            throw new HttpException('Query param "mobile" is required.', 422);
        }

        Response::json([
            'success' => true,
            'data' => $this->service->listByMobile($mobile),
        ]);
    }
}
