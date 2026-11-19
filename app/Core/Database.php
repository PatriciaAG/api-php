<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'], $config['port'], $config['database'], $config['charset']);
            try {
                self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new \RuntimeException('No se pudo conectar con la base de datos. Revisa config/database.php o las variables DB_.', 0, $e);
            }
        }
        return self::$connection;
    }
}
