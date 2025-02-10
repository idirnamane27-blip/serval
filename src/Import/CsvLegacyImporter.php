<?php
namespace Serval\Import;

use Serval\AcademicYear;
use Serval\Repository\ImportLogRepository;
use Serval\Repository\ServiceRepository;
use Serval\Repository\TeacherRepository;

/**
 * Import des fichiers CSV des autres composantes (vacataires, compléments).
 * Format : séparateur ";", virgule décimale, première ligne = noms de colonnes.
 */
class CsvLegacyImporter
{
    private $pdo;
    private $teachers;
    private $services;
    private $journal;

    public $sansEnseignant = 0;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->teachers = new TeacherRepository($pdo);
        $this->services = new ServiceRepository($pdo);
        $this->journal = new ImportLogRepository($pdo);
    }

    public function importer($fichier, $anneeDefaut = null)
    {
        $this->sansEnseignant = 0;
        $importId = $this->journal->start(basename($fichier), $anneeDefaut);
        $fh = fopen($fichier, 'r');
        if (!$fh) {
            throw new \RuntimeException('Fichier illisible : ' . $fichier);
        }
        $entete = fgetcsv($fh, 0, ';');
        $cols = array_flip(array_map('strtolower', array_map('trim', $entete)));
        $n = 0;

        $this->pdo->beginTransaction();
        try {
            while (($champs = fgetcsv($fh, 0, ';')) !== false) {
                if (count($champs) != count($entete)) {
                    continue; // ligne incomplète
                }
                $champs = array_map(array($this, 'versUtf8'), $champs);
                $matricule = isset($cols['matricule']) ? trim($champs[$cols['matricule']]) : '';
                $nom = trim($champs[$cols['nom']]) . ' ' . trim($champs[$cols['prenom']]);
                $annee = (isset($cols['annee']) && $champs[$cols['annee']] != '') ? $champs[$cols['annee']] : $anneeDefaut;
                AcademicYear::normalize($annee); // contrôle de l'année

                $enseignant = $matricule != '' ? $this->teachers->find($matricule) : null;
                if ($enseignant === null) {
                    $enseignant = $this->teachers->findByName($nom);
                }
                if ($enseignant === null) {
                    $this->sansEnseignant++;
                }
                $this->services->insert(array(
                    'annee'              => $annee,
                    'teacher_id'         => $enseignant ? $enseignant['id'] : null,
                    'teacher_nom_source' => $nom,
                    'ue'                 => $champs[$cols['ue']],
                    'libelle'            => null,
                    'type'               => $champs[$cols['type']],
                    'heures'             => (float) str_replace(',', '.', $champs[$cols['heures']]),
                    'groupes'            => $champs[$cols['groupes']],
                    'part'               => $champs[$cols['part']],
                    'etd_impose'         => null,
                    'commentaire'        => isset($cols['commentaire']) ? $champs[$cols['commentaire']] : null,
                    'source'             => 'csv:' . basename($fichier),
                    'import_id'          => $importId,
                ));
                $n++;
            }
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
        fclose($fh);
        $this->journal->finish($importId, $n, 'OK', $n . ' ligne(s)');
        return $n;
    }

    /** Les anciens fichiers sont en ISO-8859-1. */
    public function versUtf8($s)
    {
        if (!mb_check_encoding($s, 'UTF-8')) {
            $s = utf8_encode($s);
        }
        return $s;
    }
}
