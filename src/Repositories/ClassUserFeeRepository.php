<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ClassUserFeeRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function getAgreedFee(string $aadhaarNumber, int $classId): ?float
    {
        $stmt = $this->pdo->prepare(
            'SELECT agreed_fee FROM class_user_fees WHERE aadhaar_number = :aadhaar_number AND class_id = :class_id'
        );
        $stmt->execute([
            'aadhaar_number' => $aadhaarNumber,
            'class_id' => $classId,
        ]);
        $row = $stmt->fetch();
        return $row !== false ? (float) $row['agreed_fee'] : null;
    }

    /** Ensure a row exists with default fee; used on first registration. */
    public function ensureAgreedFee(string $aadhaarNumber, int $classId, float $defaultFee): void
    {
        $existing = $this->getAgreedFee($aadhaarNumber, $classId);
        if ($existing !== null) {
            return;
        }
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $now = date('Y-m-d H:i:s');
        if ($driver === 'sqlite') {
            $this->pdo->prepare(
                'INSERT INTO class_user_fees (aadhaar_number, class_id, agreed_fee, created_at, updated_at)
                 VALUES (:aadhaar_number, :class_id, :agreed_fee, :created_at, :updated_at)'
            )->execute([
                'aadhaar_number' => $aadhaarNumber,
                'class_id' => $classId,
                'agreed_fee' => $defaultFee,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $this->pdo->prepare(
                'INSERT INTO class_user_fees (aadhaar_number, class_id, agreed_fee)
                 VALUES (:aadhaar_number, :class_id, :agreed_fee)'
            )->execute([
                'aadhaar_number' => $aadhaarNumber,
                'class_id' => $classId,
                'agreed_fee' => $defaultFee,
            ]);
        }
    }

    /** Admin: set or update agreed fee for this user (aadhaar) and class. */
    public function setAgreedFee(string $aadhaarNumber, int $classId, float $agreedFee): void
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $now = date('Y-m-d H:i:s');
        if ($driver === 'sqlite') {
            $this->pdo->prepare(
                'INSERT INTO class_user_fees (aadhaar_number, class_id, agreed_fee, created_at, updated_at)
                 VALUES (:aadhaar_number, :class_id, :agreed_fee, :created_at, :updated_at)
                 ON CONFLICT(aadhaar_number, class_id) DO UPDATE SET agreed_fee = :agreed_fee2, updated_at = :updated_at2'
            )->execute([
                'aadhaar_number' => $aadhaarNumber,
                'class_id' => $classId,
                'agreed_fee' => $agreedFee,
                'created_at' => $now,
                'updated_at' => $now,
                'agreed_fee2' => $agreedFee,
                'updated_at2' => $now,
            ]);
        } else {
            $this->pdo->prepare(
                'INSERT INTO class_user_fees (aadhaar_number, class_id, agreed_fee)
                 VALUES (:aadhaar_number, :class_id, :agreed_fee)
                 ON DUPLICATE KEY UPDATE agreed_fee = VALUES(agreed_fee)'
            )->execute([
                'aadhaar_number' => $aadhaarNumber,
                'class_id' => $classId,
                'agreed_fee' => $agreedFee,
            ]);
        }
    }
}
