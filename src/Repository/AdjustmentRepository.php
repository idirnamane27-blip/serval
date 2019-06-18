<?php
namespace Serval\Repository;

/**
 * Corrections manuelles validées par l'administration (en HETD, positives ou négatives).
 */
class AdjustmentRepository
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function totalFor($teacherId, $annee)
    {
        $st = $this->pdo->prepare('SELECT SUM(heures_etd) AS total FROM adjustments WHERE annee = ? AND teacher_id = ?');
        $st->execute(array($annee, (string) $teacherId));
        $row = $st->fetch();
        return (float) $row['total'];
    }

    public function findByYear($annee)
    {
        $st = $this->pdo->prepare('SELECT * FROM adjustments WHERE annee = ? ORDER BY id');
        $st->execute(array($annee));
        return $st->fetchAll();
    }

    public function insert(array $a)
    {
        $st = $this->pdo->prepare('INSERT INTO adjustments (annee, teacher_id, heures_etd, motif, valide_par, date_validation, origine) VALUES (?, ?, ?, ?, ?, ?, ?)');
        return $st->execute(array($a['annee'], (string) $a['teacher_id'], $a['heures_etd'], $a['motif'], $a['valide_par'], $a['date_validation'], isset($a['origine']) ? $a['origine'] : 'MANUEL'));
    }
}
