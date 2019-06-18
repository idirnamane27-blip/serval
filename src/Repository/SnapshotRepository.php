<?php
namespace Serval\Repository;

/**
 * États de service figés à la clôture d'une année (états signés).
 */
class SnapshotRepository
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByYear($annee)
    {
        $st = $this->pdo->prepare('SELECT * FROM snapshots WHERE annee = ? ORDER BY nom, prenom, teacher_id');
        $st->execute(array($annee));
        return $st->fetchAll();
    }

    public function insert(array $l)
    {
        $st = $this->pdo->prepare(
            'INSERT INTO snapshots (annee, teacher_id, nom, prenom, statut, service_du, decharges, total_etd, ajustements, total_officiel, solde, observations)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        return $st->execute(array(
            $l['annee'], (string) $l['teacher_id'], $l['nom'], $l['prenom'], $l['statut'], $l['service_du'], $l['decharges'],
            $l['total_etd'], $l['ajustements'], $l['total_officiel'], $l['solde'], $l['observations'],
        ));
    }
}
