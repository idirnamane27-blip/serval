<?php
declare(strict_types=1);

namespace Serval\Tests;

/**
 * Base SQLite en mémoire construite à partir de db/schema.sql et des migrations.
 */
final class TestDatabase
{
    public static function create(): \PDO
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $pdo->exec(file_get_contents(__DIR__ . '/../db/schema.sql'));
        foreach (glob(__DIR__ . '/../db/migrations/*.sql') as $migration) {
            $pdo->exec(file_get_contents($migration));
        }
        return $pdo;
    }
}
