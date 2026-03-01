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

    public function totalPaidByAadhaarAndClass(string $aadhaarNumber, int $classId): float
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(amount_paid), 0) AS total_paid FROM class_payments WHERE aadhaar_number = :aadhaar_number AND class_id = :class_id'
        );
        $stmt->execute([
            'aadhaar_number' => $aadhaarNumber,
            'class_id' => $classId,
        ]);

        return (float) ($stmt->fetch()['total_paid'] ?? 0);
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO class_payments (mobile, aadhaar_number, name, email, class_id, preferred_time, location, siblings_name, message, amount_paid, transaction_id, transaction_msg, aadhaar_doc_path, aadhaar_doc_back_path, transaction_receipt_path, payment_status)
             VALUES (:mobile, :aadhaar_number, :name, :email, :class_id, :preferred_time, :location, :siblings_name, :message, :amount_paid, :transaction_id, :transaction_msg, :aadhaar_doc_path, :aadhaar_doc_back_path, :transaction_receipt_path, :payment_status)'
        );
        $stmt->execute([
            'mobile' => $data['mobile'],
            'aadhaar_number' => $data['aadhaar_number'],
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
                cp.aadhaar_number,
                c.id AS class_id,
                c.class_name,
                c.total_fee,
                COALESCE(SUM(cp.amount_paid), 0) AS paid_amount,
                (c.total_fee - COALESCE(SUM(cp.amount_paid), 0)) AS remaining_amount
             FROM class_payments cp
             JOIN classes c ON c.id = cp.class_id
             WHERE cp.mobile = :mobile AND c.is_active = 1
             GROUP BY cp.aadhaar_number, cp.class_id, c.id, c.class_name, c.total_fee
             ORDER BY c.id ASC, cp.aadhaar_number'
        );
        $stmt->execute(['mobile' => $mobile]);

        return $stmt->fetchAll();
    }
}
