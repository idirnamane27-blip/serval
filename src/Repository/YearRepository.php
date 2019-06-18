<?php
namespace Serval\Repository;

class YearRepository
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function find($code)
    {
        $st = $this->pdo->prepare('SELECT * FROM annees WHERE code = ?');
        $st->execute(array($code));
        $row = $st->fetch();
        return $row ? $row : null;
    }

    public function all()
    {
        return $this->pdo->query('SELECT * FROM annees ORDER BY code DESC')->fetchAll();
    }

    public function create($code)
    {
        $st = $this->pdo->prepare('INSERT INTO annees (code, cloturee) VALUES (?, 0)');
        return $st->execute(array($code));
    }

    public function cloturer($code, $date, $par, $commentaire = null)
    {
        $st = $this->pdo->prepare('UPDATE annees SET cloturee = 1, date_cloture = ?, cloture_par = ?, commentaire = ? WHERE code = ?');
        return $st->execute(array($date, $par, $commentaire, $code));
    }
}
