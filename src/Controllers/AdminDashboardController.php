<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\AdminDashboardService;

final class AdminDashboardController
{
    public function __construct(
        private readonly AdminDashboardService $service = new AdminDashboardService()
    ) {
    }

    /** GET /api/admin/dashboard - overview + payment summary. Query: start_date, end_date (Y-m-d, optional). */
    public function dashboard(Request $request): void
    {
        $params = [
            'start_date' => $request->query['start_date'] ?? null,
            'end_date' => $request->query['end_date'] ?? null,
        ];
        $data = $this->service->getDashboard($params);
        Response::json(['success' => true, 'data' => $data]);
    }

    /** GET /api/admin/course-distribution - classes with enrollment count. */
    public function courseDistribution(): void
    {
        $data = $this->service->getCourseDistribution();
        Response::json(['success' => true, 'data' => $data]);
    }

    /** GET /api/admin/recent-activity - combined recent registrations and donations. Query: limit (optional). */
    public function recentActivity(Request $request): void
    {
        $limit = isset($request->query['limit']) ? (int) $request->query['limit'] : 20;
        $limit = min(max($limit, 1), 100);
        $data = $this->service->getRecentActivity($limit);
        Response::json(['success' => true, 'data' => $data]);
    }

    /** GET /api/admin/registrations - list with search and status filter. Query: search, status (all|pending|partial|completed), limit, offset, start_date, end_date. */
    public function listRegistrations(Request $request): void
    {
        $params = [
            'search' => $request->query['search'] ?? '',
            'status' => $request->query['status'] ?? 'all',
            'limit' => $request->query['limit'] ?? 50,
            'offset' => $request->query['offset'] ?? 0,
            'start_date' => $request->query['start_date'] ?? null,
            'end_date' => $request->query['end_date'] ?? null,
        ];
        if ($params['status'] === 'all') {
            $params['status'] = '';
        }
        $result = $this->service->listRegistrations($params);
        Response::json(['success' => true, 'data' => $result['list'], 'total' => $result['total']]);
    }

    /** GET /api/admin/donations - list with search and status filter. Query: search, status (all|pending|verified|rejected), limit, offset, start_date, end_date. */
    public function listDonations(Request $request): void
    {
        $params = [
            'search' => $request->query['search'] ?? '',
            'status' => $request->query['status'] ?? 'all',
            'limit' => $request->query['limit'] ?? 50,
            'offset' => $request->query['offset'] ?? 0,
            'start_date' => $request->query['start_date'] ?? null,
            'end_date' => $request->query['end_date'] ?? null,
        ];
        if ($params['status'] === 'all') {
            $params['status'] = '';
        }
        $result = $this->service->listDonations($params);
        Response::json(['success' => true, 'data' => $result['list'], 'total' => $result['total']]);
    }

    /** GET /api/admin/healing-submissions - list with search and issue type filter. Query: search, issue_type, limit, offset, start_date, end_date. */
    public function listHealingSubmissions(Request $request): void
    {
        $params = [
            'search' => $request->query['search'] ?? '',
            'issue_type' => $request->query['issue_type'] ?? '',
            'limit' => $request->query['limit'] ?? 50,
            'offset' => $request->query['offset'] ?? 0,
            'start_date' => $request->query['start_date'] ?? null,
            'end_date' => $request->query['end_date'] ?? null,
        ];
        $result = $this->service->listHealingSubmissions($params);
        Response::json(['success' => true, 'data' => $result['list'], 'total' => $result['total']]);
    }

    /** GET /api/admin/donations/summary - total amount, verified/pending/rejected counts. Query: start_date, end_date (optional). */
    public function donationsSummary(Request $request): void
    {
        $params = [
            'start_date' => $request->query['start_date'] ?? null,
            'end_date' => $request->query['end_date'] ?? null,
        ];
        $data = $this->service->getDonationsSummary($params);
        Response::json(['success' => true, 'data' => $data]);
    }

    /** PUT /api/admin/donations/status - body: { "id": 1, "status": "verified" | "pending" | "rejected" }. */
    public function updateDonationStatus(Request $request): void
    {
        $body = $request->body ?? [];
        $id = isset($body['id']) ? (int) $body['id'] : 0;
        $status = isset($body['status']) ? trim((string) $body['status']) : '';
        if ($id <= 0) {
            throw new HttpException('Body "id" is required and must be a positive integer.', 422);
        }
        if ($status === '') {
            throw new HttpException('Body "status" is required (pending, verified, or rejected).', 422);
        }
        $data = $this->service->updateDonationStatus($id, $status);
        Response::json(['success' => true, 'message' => 'Donation status updated.', 'data' => $data]);
    }
}
