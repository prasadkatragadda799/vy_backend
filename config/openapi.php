<?php

declare(strict_types=1);

/**
 * OpenAPI 3.0 specification for Yoga API.
 * Served at GET /api/openapi.json
 */
return [
    'openapi' => '3.0.3',
    'info' => [
        'title' => 'Yoga Class & Donation API',
        'description' => 'APIs for class registration (with partial payment), payment summary, donations, and donation history.',
        'version' => '1.0.0',
    ],
    'servers' => [
        ['url' => '/', 'description' => 'Current host'],
    ],
    'paths' => [
        '/' => [
            'get' => [
                'summary' => 'Root',
                'description' => 'Service info and links.',
                'operationId' => 'root',
                'responses' => [
                    '200' => [
                        'description' => 'OK',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'service' => ['type' => 'string', 'example' => 'yoga-api'],
                                        'health' => ['type' => 'string', 'example' => '/api/health'],
                                        'docs' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/health' => [
            'get' => [
                'summary' => 'Health check',
                'description' => 'Returns service status.',
                'operationId' => 'health',
                'responses' => [
                    '200' => [
                        'description' => 'OK',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'status' => ['type' => 'string', 'example' => 'ok'],
                                        'message' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/classes' => [
            'get' => [
                'summary' => 'List classes',
                'description' => 'Returns active classes for dropdown (id, class_name, total_fee).',
                'operationId' => 'listClasses',
                'responses' => [
                    '200' => [
                        'description' => 'OK',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'data' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer'],
                                                    'class_name' => ['type' => 'string'],
                                                    'total_fee' => ['type' => 'number'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            ],
        'post' => [
                'summary' => 'Create class',
                'description' => 'Create a new class with name and amount (total_fee).',
                'operationId' => 'createClass',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['class_name', 'total_fee'],
                                'properties' => [
                                    'class_name' => ['type' => 'string', 'description' => 'Display name of the class'],
                                    'total_fee' => ['type' => 'number', 'description' => 'Amount to be paid for this class', 'minimum' => 0.01],
                                    'is_active' => ['type' => 'boolean', 'description' => 'Whether class is available for registration', 'default' => true],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'message' => ['type' => 'string'],
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => ['type' => 'integer'],
                                                'class_name' => ['type' => 'string'],
                                                'total_fee' => ['type' => 'number'],
                                                'is_active' => ['type' => 'boolean'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '422' => ['description' => 'Validation failed'],
                ],
            ],
        'put' => [
                'summary' => 'Update class',
                'description' => 'Update class name, amount (total_fee), or active status. Send at least one field.',
                'operationId' => 'updateClass',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['id'],
                                'properties' => [
                                    'id' => ['type' => 'integer', 'description' => 'Class ID to update'],
                                    'class_name' => ['type' => 'string', 'description' => 'New display name'],
                                    'total_fee' => ['type' => 'number', 'description' => 'New amount', 'minimum' => 0.01],
                                    'is_active' => ['type' => 'boolean', 'description' => 'Whether class is available'],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'OK',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'message' => ['type' => 'string'],
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => ['type' => 'integer'],
                                                'class_name' => ['type' => 'string'],
                                                'total_fee' => ['type' => 'number'],
                                                'is_active' => ['type' => 'boolean'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '404' => ['description' => 'Class not found'],
                    '422' => ['description' => 'Validation failed'],
                ],
            ],
        ],
        '/api/classes/register-payment' => [
            'post' => [
                'summary' => 'Register class & record payment',
                'description' => 'Submit registration with optional partial payment. Use multipart/form-data for Aadhaar docs. Same mobile + class_id on later submissions reduces remaining fee.',
                'operationId' => 'registerPayment',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'mobile', 'class_id', 'amount_paid', 'aadhaar_doc', 'aadhaar_doc_back', 'transaction_receipt_image'],
                                'properties' => [
                                    'name' => ['type' => 'string', 'description' => 'Full name'],
                                    'mobile' => ['type' => 'string', 'description' => '10–15 digits', 'example' => '9876543210'],
                                    'email' => ['type' => 'string', 'format' => 'email'],
                                    'class_id' => ['type' => 'integer', 'description' => 'From GET /api/classes'],
                                    'preferred_time' => ['type' => 'string'],
                                    'location' => ['type' => 'string'],
                                    'siblings_name' => ['type' => 'string'],
                                    'transaction_msg' => ['type' => 'string'],
                                    'transaction_id' => ['type' => 'string'],
                                    'message' => ['type' => 'string', 'description' => 'Additional message'],
                                    'amount_paid' => ['type' => 'number', 'description' => 'Amount paid (can be partial)'],
                                    'aadhaar_doc' => ['type' => 'string', 'format' => 'binary', 'description' => 'Aadhaar front (JPEG, PNG, WebP, PDF, max 5MB)'],
                                    'aadhaar_doc_back' => ['type' => 'string', 'format' => 'binary', 'description' => 'Aadhaar back (JPEG, PNG, WebP, PDF, max 5MB)'],
                                    'transaction_receipt_image' => ['type' => 'string', 'format' => 'binary', 'description' => 'Transaction receipt image (JPEG, PNG, WebP, PDF, max 5MB)'],
                                ],
                            ],
                        ],
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'mobile', 'class_id', 'amount_paid'],
                                'properties' => [
                                    'name' => ['type' => 'string'],
                                    'mobile' => ['type' => 'string'],
                                    'email' => ['type' => 'string'],
                                    'class_id' => ['type' => 'integer'],
                                    'preferred_time' => ['type' => 'string'],
                                    'location' => ['type' => 'string'],
                                    'siblings_name' => ['type' => 'string'],
                                    'transaction_msg' => ['type' => 'string'],
                                    'transaction_id' => ['type' => 'string'],
                                    'message' => ['type' => 'string'],
                                    'amount_paid' => ['type' => 'number'],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'message' => ['type' => 'string'],
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'payment_id' => ['type' => 'integer'],
                                                'class_id' => ['type' => 'integer'],
                                                'class_name' => ['type' => 'string'],
                                                'total_fee' => ['type' => 'number'],
                                                'amount_paid_now' => ['type' => 'number'],
                                                'paid_till_now' => ['type' => 'number'],
                                                'remaining_amount' => ['type' => 'number'],
                                                'payment_status' => ['type' => 'string', 'enum' => ['partial', 'paid']],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '404' => ['description' => 'Class not found'],
                    '409' => ['description' => 'Fee already fully paid for this mobile + class'],
                    '422' => ['description' => 'Validation failed or missing Aadhaar docs'],
                ],
            ],
        ],
        '/api/classes/payment-summary' => [
            'get' => [
                'summary' => 'Payment summary by mobile',
                'description' => 'Returns per-class paid/remaining amounts for the given mobile.',
                'operationId' => 'paymentSummary',
                'parameters' => [
                    [
                        'name' => 'mobile',
                        'in' => 'query',
                        'required' => true,
                        'schema' => ['type' => 'string', 'example' => '9876543210'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'OK',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'data' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'class_id' => ['type' => 'integer'],
                                                    'class_name' => ['type' => 'string'],
                                                    'total_fee' => ['type' => 'number'],
                                                    'paid_amount' => ['type' => 'number'],
                                                    'remaining_amount' => ['type' => 'number'],
                                                    'payment_status' => ['type' => 'string'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '422' => ['description' => 'Missing mobile'],
                ],
            ],
        ],
        '/api/donations' => [
            'post' => [
                'summary' => 'Submit donation',
                'description' => 'Requires multipart/form-data with Aadhaar and transaction doc uploads.',
                'operationId' => 'createDonation',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'mobile', 'amount_paid', 'aadhaar_front_doc', 'aadhaar_back_doc', 'transaction_rep_doc'],
                                'properties' => [
                                    'name' => ['type' => 'string'],
                                    'mobile' => ['type' => 'string', 'example' => '9876543210'],
                                    'amount_paid' => ['type' => 'number'],
                                    'transaction_id' => ['type' => 'string'],
                                    'aadhaar_front_doc' => ['type' => 'string', 'format' => 'binary'],
                                    'aadhaar_back_doc' => ['type' => 'string', 'format' => 'binary'],
                                    'transaction_rep_doc' => ['type' => 'string', 'format' => 'binary'],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'message' => ['type' => 'string'],
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'donation_id' => ['type' => 'integer'],
                                                'amount_paid' => ['type' => 'number'],
                                                'aadhaar_front_path' => ['type' => 'string'],
                                                'aadhaar_back_path' => ['type' => 'string'],
                                                'transaction_rep_path' => ['type' => 'string'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '422' => ['description' => 'Validation failed or missing files'],
                ],
            ],
            'get' => [
                'summary' => 'Donation history by mobile',
                'operationId' => 'listDonations',
                'parameters' => [
                    [
                        'name' => 'mobile',
                        'in' => 'query',
                        'required' => true,
                        'schema' => ['type' => 'string', 'example' => '9876543210'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'OK',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'data' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer'],
                                                    'name' => ['type' => 'string'],
                                                    'mobile' => ['type' => 'string'],
                                                    'amount_paid' => ['type' => 'number'],
                                                    'transaction_id' => ['type' => 'string'],
                                                    'aadhaar_front_path' => ['type' => 'string'],
                                                    'aadhaar_back_path' => ['type' => 'string'],
                                                    'transaction_rep_path' => ['type' => 'string'],
                                                    'created_at' => ['type' => 'string'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '422' => ['description' => 'Missing mobile'],
                ],
            ],
        ],
        '/api/openapi.json' => [
            'get' => [
                'summary' => 'OpenAPI spec',
                'description' => 'This OpenAPI 3.0 specification (JSON).',
                'operationId' => 'openapiJson',
                'responses' => [
                    '200' => [
                        'description' => 'OpenAPI 3.0 JSON',
                        'content' => ['application/json' => ['schema' => ['type' => 'object']]],
                    ],
                ],
            ],
        ],
    ],
];
