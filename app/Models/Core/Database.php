<?php

class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function connect(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $configPath = dirname(__DIR__, 3) . '/config/database.php';

        if (!is_file($configPath)) {
            throw new RuntimeException('Database configuration file is missing.');
        }

        $config = require $configPath;
        $charset = $config['charset'] ?? 'utf8mb4';
        $port = (int) ($config['port'] ?? 3306);
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $port,
            $config['database'],
            $charset
        );

        self::$connection = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$connection;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $statement = self::connect()->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    public static function exec(string $sql, array $params = []): int
    {
        return self::query($sql, $params)->rowCount();
    }

    public static function lastInsertId(): string
    {
        return self::connect()->lastInsertId();
    }
}
