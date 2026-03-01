<?php

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../src/bootstrap.php';

use App\Controllers\ClassController;
use App\Controllers\ClassRegistrationController;
use App\Controllers\DonationController;
use App\Core\ExceptionHandler;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

$router = new Router();
$classController = new ClassRegistrationController();
$classCrudController = new ClassController();
$donationController = new DonationController();

$router->get('/', static function (): void {
    Response::json([
        'service' => 'yoga-api',
        'health' => '/api/health',
        'docs' => '/api/docs',
    ]);
});

$router->get('/api/health', static function (): void {
    Response::json([
        'status' => 'ok',
        'message' => 'Backend is running.',
    ]);
});

$router->get('/api/docs', static function (): void {
    header('Content-Type: text/html; charset=utf-8');
    $spec = require __DIR__ . '/../config/openapi.php';
    $specJson = json_encode($spec, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $html = file_get_contents(__DIR__ . '/swagger-ui.html');
    // Embed spec so Swagger UI doesn't need a separate request (avoids 404 on Railway/proxies)
    $html = str_replace(
        '</head>',
        '<script>window.OPENAPI_SPEC = ' . $specJson . ';</script></head>',
        $html
    );
    echo $html;
    exit;
});

$router->get('/api/openapi.json', static function (): void {
    $spec = require __DIR__ . '/../config/openapi.php';
    Response::json($spec);
});

$router->get('/openapi.json', static function (): void {
    $spec = require __DIR__ . '/../config/openapi.php';
    Response::json($spec);
});

$router->get('/api/classes', [$classController, 'listClasses']);
$router->post('/api/classes', [$classCrudController, 'createClass']);
$router->put('/api/classes', [$classCrudController, 'updateClass']);
$router->post('/api/classes/register-payment', [$classController, 'registerPayment']);
$router->get('/api/classes/payment-summary', [$classController, 'paymentSummary']);

$router->post('/api/donations', [$donationController, 'store']);
$router->get('/api/donations', [$donationController, 'listByMobile']);

try {
    $request = Request::fromGlobals();
    $router->dispatch($request);
} catch (Throwable $exception) {
    ExceptionHandler::handle($exception);
}
