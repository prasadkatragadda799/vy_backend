<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class YogaFormRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO yoga_form_submissions (
                author_name,
                father_or_mother_name,
                course_name,
                year_of_learning,
                qualification,
                previous_course,
                sibling_details,
                age_or_birth_date,
                location,
                aadhaar_front_path,
                aadhaar_back_path,
                mentor_name,
                mentor_occupation,
                mentor_phone,
                referrer_name,
                referrer_phone,
                referrer_occupation,
                another_referrer_name,
                another_referrer_phone,
                another_referrer_occupation,
                amount_paid,
                transaction_id,
                transaction_receipt_path,
                additional_message,
                mobile
            ) VALUES (
                :author_name,
                :father_or_mother_name,
                :course_name,
                :year_of_learning,
                :qualification,
                :previous_course,
                :sibling_details,
                :age_or_birth_date,
                :location,
                :aadhaar_front_path,
                :aadhaar_back_path,
                :mentor_name,
                :mentor_occupation,
                :mentor_phone,
                :referrer_name,
                :referrer_phone,
                :referrer_occupation,
                :another_referrer_name,
                :another_referrer_phone,
                :another_referrer_occupation,
                :amount_paid,
                :transaction_id,
                :transaction_receipt_path,
                :additional_message,
                :mobile
            )'
        );

        $stmt->execute([
            'author_name' => $data['author_name'],
            'father_or_mother_name' => $data['father_or_mother_name'],
            'course_name' => $data['course_name'],
            'year_of_learning' => $data['year_of_learning'] ?? null,
            'qualification' => $data['qualification'],
            'previous_course' => $data['previous_course'] ?? null,
            'sibling_details' => $data['sibling_details'] ?? null,
            'age_or_birth_date' => $data['age_or_birth_date'],
            'location' => $data['location'],
            'aadhaar_front_path' => $data['aadhaar_front_path'],
            'aadhaar_back_path' => $data['aadhaar_back_path'],
            'mentor_name' => $data['mentor_name'] ?? null,
            'mentor_occupation' => $data['mentor_occupation'] ?? null,
            'mentor_phone' => $data['mentor_phone'] ?? null,
            'referrer_name' => $data['referrer_name'] ?? null,
            'referrer_phone' => $data['referrer_phone'] ?? null,
            'referrer_occupation' => $data['referrer_occupation'] ?? null,
            'another_referrer_name' => $data['another_referrer_name'] ?? null,
            'another_referrer_phone' => $data['another_referrer_phone'] ?? null,
            'another_referrer_occupation' => $data['another_referrer_occupation'] ?? null,
            'amount_paid' => $data['amount_paid'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'transaction_receipt_path' => $data['transaction_receipt_path'],
            'additional_message' => $data['additional_message'] ?? null,
            'mobile' => $data['mobile'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function listByMobile(string $mobile): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, author_name, father_or_mother_name, course_name, year_of_learning, qualification, previous_course,
                    sibling_details, age_or_birth_date, location, mentor_name, mentor_occupation, mentor_phone, referrer_name,
                    referrer_phone, referrer_occupation, another_referrer_name, another_referrer_phone, another_referrer_occupation,
                    amount_paid, transaction_id, additional_message, aadhaar_front_path, aadhaar_back_path, transaction_receipt_path,
                    mobile, created_at
             FROM yoga_form_submissions
             WHERE mobile = :mobile
             ORDER BY created_at DESC'
        );
        $stmt->execute(['mobile' => $mobile]);

        return $stmt->fetchAll();
    }
}
