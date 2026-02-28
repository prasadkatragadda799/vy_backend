<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    /** @param array<string, mixed> $files Normalized file entries from $_FILES */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $body,
        public readonly array $files = []
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $config = require __DIR__ . '/../../config/config.php';
        $basePath = (string) (($config['app']['base_path'] ?? ''));
        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }
        $query = $_GET ?? [];

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $rawBody = file_get_contents('php://input');
        $decodedJson = [];

        if (str_contains(strtolower($contentType), 'application/json') && $rawBody) {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $decodedJson = $decoded;
            }
        }

        $body = array_merge($_POST ?? [], $decodedJson);

        $files = self::normalizeFiles($_FILES ?? []);

        return new self($method, $path, $query, $body, $files);
    }

    /**
     * @param array<string, mixed> $raw
     * @return array<string, array{name: string, type: string, tmp_name: string, error: int, size: int}>
     */
    private static function normalizeFiles(array $raw): array
    {
        $out = [];
        foreach ($raw as $key => $entry) {
            if (is_array($entry) && isset($entry['name'], $entry['type'], $entry['tmp_name'], $entry['error'], $entry['size'])) {
                $out[$key] = [
                    'name' => (string) $entry['name'],
                    'type' => (string) $entry['type'],
                    'tmp_name' => (string) $entry['tmp_name'],
                    'error' => (int) $entry['error'],
                    'size' => (int) $entry['size'],
                ];
            } elseif (is_array($entry) && isset($entry['name']) && is_array($entry['name'])) {
                foreach (array_keys($entry['name']) as $i) {
                    $out[$key . '_' . $i] = [
                        'name' => (string) ($entry['name'][$i] ?? ''),
                        'type' => (string) ($entry['type'][$i] ?? ''),
                        'tmp_name' => (string) ($entry['tmp_name'][$i] ?? ''),
                        'error' => (int) ($entry['error'][$i] ?? UPLOAD_ERR_NO_FILE),
                        'size' => (int) ($entry['size'][$i] ?? 0),
                    ];
                }
            }
        }
        return $out;
    }
}
