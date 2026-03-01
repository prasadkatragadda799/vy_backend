<?php

declare(strict_types=1);

/**
 * OpenAPI 3.0 spec for Yoga API. Served at GET /api/openapi.json and embedded in /api/docs.
 */

$paths = [];

$paths['/'] = [
    'get' => [
        'summary' => 'Root',
        'operationId' => 'root',
        'responses' => ['200' => ['description' => 'OK', 'content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => ['service' => ['type' => 'string'], 'health' => ['type' => 'string'], 'docs' => ['type' => 'string']]]]]]],
    ],
];

$paths['/api/health'] = [
    'get' => [
        'summary' => 'Health check',
        'operationId' => 'health',
        'responses' => ['200' => ['description' => 'OK', 'content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => ['status' => ['type' => 'string'], 'message' => ['type' => 'string']]]]]]],
    ],
];

$paths['/api/classes'] = [
    'get' => [
        'summary' => 'List classes',
        'operationId' => 'listClasses',
        'responses' => ['200' => ['description' => 'OK', 'content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => ['success' => ['type' => 'boolean'], 'data' => ['type' => 'array', 'items' => ['type' => 'object', 'properties' => ['id' => ['type' => 'integer'], 'class_name' => ['type' => 'string'], 'total_fee' => ['type' => 'number']]]]]]]]]],
    ],
    'post' => [
        'summary' => 'Create class',
        'operationId' => 'createClass',
        'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['type' => 'object', 'required' => ['class_name', 'total_fee'], 'properties' => ['class_name' => ['type' => 'string'], 'total_fee' => ['type' => 'number', 'minimum' => 0.01], 'is_active' => ['type' => 'boolean']]]]]],
        'responses' => ['201' => ['description' => 'Created'], '422' => ['description' => 'Validation failed']],
    ],
    'put' => [
        'summary' => 'Update class',
        'operationId' => 'updateClass',
        'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['type' => 'object', 'required' => ['id'], 'properties' => ['id' => ['type' => 'integer'], 'class_name' => ['type' => 'string'], 'total_fee' => ['type' => 'number'], 'is_active' => ['type' => 'boolean']]]]]],
        'responses' => ['200' => ['description' => 'OK'], '404' => ['description' => 'Class not found'], '422' => ['description' => 'Validation failed']],
    ],
];

$paths['/api/classes/register-payment'] = [
    'post' => [
        'summary' => 'Register class & record payment',
        'operationId' => 'registerPayment',
        'requestBody' => [
            'required' => true,
            'content' => [
                'multipart/form-data' => [
                    'schema' => [
                        'type' => 'object',
                        'required' => ['name', 'mobile', 'class_id', 'amount_paid', 'aadhaar_doc', 'aadhaar_doc_back', 'transaction_receipt_image'],
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
                            'aadhaar_doc' => ['type' => 'string', 'format' => 'binary'],
                            'aadhaar_doc_back' => ['type' => 'string', 'format' => 'binary'],
                            'transaction_receipt_image' => ['type' => 'string', 'format' => 'binary'],
                        ],
                    ],
                ],
            ],
        ],
        'responses' => ['201' => ['description' => 'Created'], '404' => ['description' => 'Class not found'], '409' => ['description' => 'Already paid'], '422' => ['description' => 'Validation failed']],
    ],
];

$paths['/api/classes/payment-summary'] = [
    'get' => [
        'summary' => 'Payment summary by mobile',
        'operationId' => 'paymentSummary',
        'parameters' => [['name' => 'mobile', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'string']]],
        'responses' => ['200' => ['description' => 'OK'], '422' => ['description' => 'Missing mobile']],
    ],
];

$paths['/api/donations'] = [
    'post' => [
        'summary' => 'Submit donation',
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
                            'mobile' => ['type' => 'string'],
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
        'responses' => ['201' => ['description' => 'Created'], '422' => ['description' => 'Validation failed']],
    ],
    'get' => [
        'summary' => 'Donation history by mobile',
        'operationId' => 'listDonations',
        'parameters' => [['name' => 'mobile', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'string']]],
        'responses' => ['200' => ['description' => 'OK'], '422' => ['description' => 'Missing mobile']],
    ],
];

$paths['/api/openapi.json'] = [
    'get' => [
        'summary' => 'OpenAPI spec',
        'operationId' => 'openapiJson',
        'responses' => ['200' => ['description' => 'OpenAPI 3.0 JSON']],
    ],
];

return [
    'openapi' => '3.0.3',
    'info' => [
        'title' => 'Yoga Class & Donation API',
        'description' => 'APIs for class registration (with partial payment), payment summary, donations, and donation history.',
        'version' => '1.0.0',
    ],
    'servers' => [['url' => '/', 'description' => 'Current host']],
    'paths' => $paths,
];
