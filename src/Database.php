<?php
namespace Serval;

/**
 * Accès à la base SQLite (connexion unique).
 */
class Database
{
    private static $pdo = null;

    public static function get()
    {
        if (self::$pdo === null) {
            $config = self::config();
            $chemin = getenv('SERVAL_DB') ? getenv('SERVAL_DB') : $config['db_path'];
            self::$pdo = new \PDO('sqlite:' . $chemin);
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
        return self::$pdo;
    }

    public static function config()
    {
        return require __DIR__ . '/../config/config.php';
    }

    /** Utilisé par les tests. */
    public static function reset()
    {
        self::$pdo = null;
    }
}
