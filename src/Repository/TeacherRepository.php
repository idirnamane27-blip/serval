<?php
namespace Serval\Repository;

class TeacherRepository
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function find($id)
    {
        $st = $this->pdo->prepare('SELECT * FROM teachers WHERE id = ?');
        $st->execute(array((string) $id));
        $row = $st->fetch();
        return $row ? $row : null;
    }

    public function all()
    {
        return $this->pdo->query('SELECT * FROM teachers ORDER BY id')->fetchAll();
    }

    public function allByName()
    {
        return $this->pdo->query('SELECT * FROM teachers ORDER BY nom, prenom, id')->fetchAll();
    }

    public static function normaliserNom($nom)
    {
        $nom = preg_replace('/\s+/', ' ', trim($nom));
        return mb_strtoupper($nom, 'UTF-8');
    }

    /**
     * Recherche par "NOM Prénom" : sert aux anciens classeurs qui n'ont pas de matricule.
     * Le nom complet identifie l'enseignant (cf. README).
     */
    public function findByName($nomComplet)
    {
        $cible = self::normaliserNom($nomComplet);
        foreach ($this->all() as $t) {
            if (self::normaliserNom($t['nom'] . ' ' . $t['prenom']) == $cible) {
                return $t;
            }
        }
        return null;
    }

    public function save(array $t)
    {
        $st = $this->pdo->prepare('INSERT OR REPLACE INTO teachers (id, nom, prenom, statut, service_du, email, actif) VALUES (?, ?, ?, ?, ?, ?, 1)');
        return $st->execute(array((string) $t['id'], $t['nom'], $t['prenom'], $t['statut'], $t['service_du'], isset($t['email']) ? $t['email'] : null));
    }
}
