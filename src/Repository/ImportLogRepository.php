<?php
namespace Serval\Repository;

/**
 * Journal des imports (table import_log).
 */
class ImportLogRepository
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function start($fichier, $annee)
    {
        $st = $this->pdo->prepare("INSERT INTO import_log (fichier, annee, statut) VALUES (?, ?, 'STARTED')");
        $st->execute(array($fichier, $annee));
        return $this->pdo->lastInsertId();
    }

    public function finish($id, $nbLignes, $statut, $message = null, $annee = null)
    {
        $st = $this->pdo->prepare('UPDATE import_log SET statut = ?, nb_lignes = ?, message = ?, annee = COALESCE(?, annee), finished_at = ? WHERE id = ?');
        return $st->execute(array($statut, $nbLignes, $message, $annee, date('Y-m-d H:i:s'), $id));
    }

    public function all()
    {
        return $this->pdo->query('SELECT * FROM import_log ORDER BY id DESC')->fetchAll();
    }
}
