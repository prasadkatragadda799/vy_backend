<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $pdo = null;
    private static array $config = [];

    public static function bootstrap(): void
    {
        self::connection();
        self::runMigrations();
        self::seedClasses();
    }

    public static function connection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $config = require __DIR__ . '/../../config/config.php';
        self::$config = $config['db'] ?? [];
        $driver = strtolower((string) (self::$config['driver'] ?? 'sqlite'));

        try {
            self::$pdo = match ($driver) {
                'mysql' => self::connectMySql(),
                'sqlite' => self::connectSqlite(),
                default => throw new RuntimeException('Unsupported DB driver: ' . $driver),
            };
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            throw new RuntimeException('Failed to connect database: ' . $exception->getMessage());
        }

        return self::$pdo;
    }

    private static function runMigrations(): void
    {
        $driver = strtolower((string) (self::$config['driver'] ?? 'sqlite'));
        $sqlPath = $driver === 'mysql'
            ? __DIR__ . '/../../database/schema_mysql.sql'
            : __DIR__ . '/../../database/schema.sql';

        if (!is_file($sqlPath)) {
            throw new RuntimeException('Schema file not found.');
        }

        $sql = file_get_contents($sqlPath);
        if ($sql === false) {
            throw new RuntimeException('Could not read schema file.');
        }

        self::connection()->exec($sql);
    }

    private static function seedClasses(): void
    {
        $checkStmt = self::connection()->query('SELECT COUNT(*) AS count FROM classes');
        $count = (int) ($checkStmt->fetch()['count'] ?? 0);

        if ($count > 0) {
            return;
        }

        $seedPath = __DIR__ . '/../../database/seed_classes.sql';
        if (!is_file($seedPath)) {
            return;
        }

        $sql = file_get_contents($seedPath);
        if ($sql === false) {
            return;
        }

        self::connection()->exec($sql);
    }

    private static function connectSqlite(): PDO
    {
        $databasePath = self::$config['database'] ?? null;
        if (!$databasePath) {
            throw new RuntimeException('Database path missing in config.');
        }

        $dir = dirname((string) $databasePath);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException('Could not create database directory.');
        }

        return new PDO('sqlite:' . $databasePath);
    }

    private static function connectMySql(): PDO
    {
        $host = (string) (self::$config['host'] ?? 'localhost');
        $port = (int) (self::$config['port'] ?? 3306);
        $database = (string) (self::$config['database'] ?? '');
        $username = (string) (self::$config['username'] ?? '');
        $password = (string) (self::$config['password'] ?? '');
        $charset = (string) (self::$config['charset'] ?? 'utf8mb4');

        if ($database === '' || $username === '') {
            throw new RuntimeException('MySQL database name and username are required.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $port,
            $database,
            $charset
        );

        return new PDO($dsn, $username, $password);
    }
}
