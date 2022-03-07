<?php
namespace Serval\Repository;

/**
 * Décharges de service (CRCT, nouveaux MCF, direction...) : diminuent le service dû.
 */
class DechargeRepository
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function totalFor($teacherId, $annee)
    {
        $st = $this->pdo->prepare('SELECT SUM(heures) AS total FROM decharges WHERE annee = ? AND teacher_id = ?');
        $st->execute(array($annee, (string) $teacherId));
        $row = $st->fetch();
        return (float) $row['total'];
    }
}
