<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class DonationRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO donations (name, mobile, aadhaar_number, amount_paid, transaction_id, aadhaar_front_path, aadhaar_back_path, transaction_rep_path)
             VALUES (:name, :mobile, :aadhaar_number, :amount_paid, :transaction_id, :aadhaar_front_path, :aadhaar_back_path, :transaction_rep_path)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'aadhaar_number' => $data['aadhaar_number'],
            'amount_paid' => $data['amount_paid'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'aadhaar_front_path' => $data['aadhaar_front_path'] ?? null,
            'aadhaar_back_path' => $data['aadhaar_back_path'] ?? null,
            'transaction_rep_path' => $data['transaction_rep_path'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function listByMobile(string $mobile): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, mobile, aadhaar_number, amount_paid, transaction_id, aadhaar_front_path, aadhaar_back_path, transaction_rep_path, created_at
             FROM donations WHERE mobile = :mobile ORDER BY id DESC'
        );
        $stmt->execute(['mobile' => $mobile]);

        return $stmt->fetchAll();
    }
}
