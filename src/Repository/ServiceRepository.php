<?php
namespace Serval\Repository;

use Serval\AcademicYear;

class ServiceRepository
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Lignes d'une année (toutes écritures de l'année confondues). */
    public function findByYear($annee)
    {
        $variantes = AcademicYear::variants($annee);
        $in = implode(',', array_fill(0, count($variantes), '?'));
        $st = $this->pdo->prepare('SELECT * FROM services WHERE annee IN (' . $in . ') ORDER BY id');
        $st->execute($variantes);
        return $st->fetchAll();
    }

    public function findByTeacherAndYear($teacherId, $annee)
    {
        $variantes = AcademicYear::variants($annee);
        $in = implode(',', array_fill(0, count($variantes), '?'));
        $st = $this->pdo->prepare('SELECT * FROM services WHERE annee IN (' . $in . ') AND teacher_id = ? ORDER BY id');
        $st->execute(array_merge($variantes, array((string) $teacherId)));
        return $st->fetchAll();
    }

    public function insert(array $l)
    {
        $st = $this->pdo->prepare(
            'INSERT INTO services (annee, teacher_id, teacher_nom_source, ue, libelle, type, heures, groupes, part, etd_impose, commentaire, source, import_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        return $st->execute(array(
            $l['annee'],
            $l['teacher_id'],
            isset($l['teacher_nom_source']) ? $l['teacher_nom_source'] : null,
            $l['ue'],
            isset($l['libelle']) ? $l['libelle'] : null,
            $l['type'],
            $l['heures'],
            $l['groupes'],
            $l['part'],
            isset($l['etd_impose']) ? $l['etd_impose'] : null,
            isset($l['commentaire']) ? $l['commentaire'] : null,
            isset($l['source']) ? $l['source'] : null,
            isset($l['import_id']) ? $l['import_id'] : null,
        ));
    }
}
