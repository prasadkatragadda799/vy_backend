<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\HealingFormService;
use App\Validation\Validator;

final class HealingFormController
{
    public function __construct(
        private readonly HealingFormService $service = new HealingFormService()
    ) {
    }

    public function submit(Request $request): void
    {
        $validated = Validator::validate($request->body, [
            'full_name' => 'required',
            'date_of_birth' => 'required',
            'mobile' => 'required|mobile',
            'aadhaar_number' => 'required|aadhaar',
            'amount_paid' => 'required|numeric',
        ]);

        $validated['time_of_birth'] = $this->clean($request->body['time_of_birth'] ?? null);
        $validated['place_of_birth'] = $this->clean($request->body['place_of_birth'] ?? null);
        $validated['current_location'] = $this->clean($request->body['current_location'] ?? null);
        $validated['email'] = $this->clean($request->body['email'] ?? null);
        $validated['address'] = $this->clean($request->body['address'] ?? null);
        $validated['issue_type'] = $this->clean($request->body['issue_type'] ?? null);
        $validated['issue_description'] = $this->clean($request->body['issue_description'] ?? null);
        $validated['transaction_id'] = $this->clean($request->body['transaction_id'] ?? null);

        $declared = strtolower((string) ($request->body['declaration_accepted'] ?? ''));
        $validated['declaration_accepted'] = in_array($declared, ['1', 'true', 'yes', 'on'], true) ? 1 : 0;
        if ($validated['declaration_accepted'] !== 1) {
            throw new HttpException('Declaration must be accepted.', 422);
        }

        $data = $this->service->submit($validated, $request->files);
        Response::json([
            'success' => true,
            'message' => 'Healing form submitted successfully.',
            'data' => $data,
        ], 201);
    }

    public function listByMobile(Request $request): void
    {
        $mobile = trim((string) ($request->query['mobile'] ?? ''));
        if ($mobile === '') {
            throw new HttpException('Query param "mobile" is required.', 422);
        }
        if (!preg_match('/^[0-9]{10,15}$/', $mobile)) {
            throw new HttpException('Query param "mobile" must be 10-15 digits.', 422);
        }

        Response::json([
            'success' => true,
            'data' => $this->service->listByMobile($mobile),
        ]);
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }
}
