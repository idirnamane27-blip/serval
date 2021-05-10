<?php
namespace Serval\Repository;

/**
 * Cache des totaux affichés par l'interface web.
 * Le cache est invalidé à chaque import et recalculé chaque nuit (cron).
 */
class CacheRepository
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get($annee, $teacherId)
    {
        $st = $this->pdo->prepare('SELECT total_etd FROM cache_totaux WHERE annee = ? AND teacher_id = ?');
        $st->execute(array($annee, (string) $teacherId));
        $row = $st->fetch();
        return $row ? (float) $row['total_etd'] : null;
    }

    public function put($annee, $teacherId, $total, $nbLignes)
    {
        $st = $this->pdo->prepare('INSERT OR REPLACE INTO cache_totaux (annee, teacher_id, total_etd, nb_lignes, computed_at) VALUES (?, ?, ?, ?, ?)');
        return $st->execute(array($annee, (string) $teacherId, $total, $nbLignes, date('Y-m-d H:i:s')));
    }

    public function invalidate($annee)
    {
        $st = $this->pdo->prepare('DELETE FROM cache_totaux WHERE annee = ?');
        return $st->execute(array($annee));
    }

    public function clear()
    {
        return $this->pdo->exec('DELETE FROM cache_totaux');
    }
}
