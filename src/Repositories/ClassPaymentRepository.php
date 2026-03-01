<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ClassPaymentRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function totalPaidByMobileAndClass(string $mobile, int $classId): float
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(amount_paid), 0) AS total_paid FROM class_payments WHERE mobile = :mobile AND class_id = :class_id'
        );
        $stmt->execute([
            'mobile' => $mobile,
            'class_id' => $classId,
        ]);

        return (float) ($stmt->fetch()['total_paid'] ?? 0);
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO class_payments (mobile, name, email, class_id, preferred_time, location, siblings_name, message, amount_paid, transaction_id, transaction_msg, aadhaar_doc_path, aadhaar_doc_back_path, transaction_receipt_path, payment_status)
             VALUES (:mobile, :name, :email, :class_id, :preferred_time, :location, :siblings_name, :message, :amount_paid, :transaction_id, :transaction_msg, :aadhaar_doc_path, :aadhaar_doc_back_path, :transaction_receipt_path, :payment_status)'
        );
        $stmt->execute([
            'mobile' => $data['mobile'],
            'name' => $data['name'],
            'email' => $data['email'],
            'class_id' => $data['class_id'],
            'preferred_time' => $data['preferred_time'] ?? null,
            'location' => $data['location'] ?? null,
            'siblings_name' => $data['siblings_name'] ?? null,
            'message' => $data['message'] ?? null,
            'amount_paid' => $data['amount_paid'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'transaction_msg' => $data['transaction_msg'] ?? null,
            'aadhaar_doc_path' => $data['aadhaar_doc_path'] ?? null,
            'aadhaar_doc_back_path' => $data['aadhaar_doc_back_path'] ?? null,
            'transaction_receipt_path' => $data['transaction_receipt_path'] ?? null,
            'payment_status' => $data['payment_status'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function summaryByMobile(string $mobile): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                c.id AS class_id,
                c.class_name,
                c.total_fee,
                COALESCE(SUM(cp.amount_paid), 0) AS paid_amount,
                (c.total_fee - COALESCE(SUM(cp.amount_paid), 0)) AS remaining_amount
             FROM classes c
             LEFT JOIN class_payments cp ON cp.class_id = c.id AND cp.mobile = :mobile
             WHERE c.is_active = 1
             GROUP BY c.id, c.class_name, c.total_fee
             ORDER BY c.id ASC'
        );
        $stmt->execute(['mobile' => $mobile]);

        return $stmt->fetchAll();
    }
}
