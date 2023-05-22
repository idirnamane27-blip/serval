<?php
namespace Serval\Import;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Serval\AcademicYear;
use Serval\Repository\ImportLogRepository;
use Serval\Repository\ServiceRepository;
use Serval\Repository\TeacherRepository;
use Serval\Repository\YearRepository;

/**
 * Import des classeurs de services (un format par époque : 2018, 2019, 2023, 2024+).
 */
class ExcelServiceImporter
{
    private $pdo;
    private $teachers;
    private $services;
    private $journal;
    private $annees;
    private $anneesVerifiees = array();
    private $derniereAnnee = null;

    public $sansEnseignant = 0;
    public $forcerArchive = false;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->teachers = new TeacherRepository($pdo);
        $this->services = new ServiceRepository($pdo);
        $this->journal = new ImportLogRepository($pdo);
        $this->annees = new YearRepository($pdo);
    }

    public function importer($fichier, $anneeForcee = null)
    {
        if (!is_readable($fichier)) {
            throw new \RuntimeException('Fichier illisible : ' . $fichier);
        }
        $this->sansEnseignant = 0;
        $this->anneesVerifiees = array();
        $this->derniereAnnee = null;

        // journal des imports
        $importId = $this->journal->start(basename($fichier), $anneeForcee);
        $classeur = IOFactory::load($fichier);
        $source = 'excel:' . basename($fichier);

        $this->pdo->beginTransaction();
        try {
            // le format est reconnu au nom de la feuille
            if ($classeur->getSheetByName('Service') !== null) {
                $n = $this->importerFormat2018($classeur, $importId, $source, $anneeForcee, $fichier);
            } elseif ($classeur->getSheetByName('SERVICE') !== null) {
                $n = $this->importerFormat2019($classeur, $importId, $source, $anneeForcee, $fichier);
            } elseif ($classeur->getSheetByName('Interventions') !== null) {
                $n = $this->importerFormat2023($classeur, $importId, $source, $anneeForcee, $fichier);
            } else {
                throw new \RuntimeException('Aucune feuille de services reconnue dans ' . basename($fichier));
            }
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        $message = $n . ' ligne(s)';
        if ($this->sansEnseignant > 0) {
            $message .= ', ' . $this->sansEnseignant . ' sans enseignant';
        }
        $this->journal->finish($importId, $n, 'OK', $message, $this->derniereAnnee);
        return $n;
    }

    /** Valeur d'une cellule telle qu'affichée (conserve les matricules du type 001245). */
    private function cellule($feuille, $col, $ligne)
    {
        $v = $feuille->getCellByColumnAndRow($col, $ligne)->getFormattedValue();
        $v = trim((string) $v);
        return $v === '' ? null : $v;
    }

    private function nombre($v)
    {
        if ($v === null) {
            return null;
        }
        return (float) str_replace(',', '.', $v);
    }

    private function annee($forcee, $valeur, $classeur, $fichier)
    {
        if ($forcee !== null && $forcee !== '') {
            $annee = AcademicYear::normalize($forcee);
        } elseif ($valeur !== null) {
            $annee = AcademicYear::normalize($valeur);
        } else {
            $annee = null;
            $p = $classeur->getSheetByName('Parametres');
            if ($p !== null) {
                for ($r = 1; $r <= 20; $r++) {
                    $lib = $this->cellule($p, 1, $r);
                    if ($lib !== null && strpos($lib, 'Année') === 0) {
                        $annee = AcademicYear::normalize($this->cellule($p, 2, $r));
                        break;
                    }
                }
            }
            if ($annee === null && preg_match('/(\d{4})[-_](\d{4})/', basename($fichier), $m)) {
                $annee = AcademicYear::normalize($m[1] . '-' . $m[2]);
            }
            if ($annee === null) {
                throw new \RuntimeException('Annee universitaire introuvable dans ' . basename($fichier));
            }
        }
        $this->verifierAnnee($annee);
        return $annee;
    }

    private function verifierAnnee($annee)
    {
        if (isset($this->anneesVerifiees[$annee])) {
            return;
        }
        $info = $this->annees->find($annee);
        if ($info === null) {
            $this->annees->create($annee);
        } elseif ($info['cloturee'] === '1' && !$this->forcerArchive) {
            throw new \RuntimeException('Annee ' . $annee . " cloturee : import refuse (option --archive pour une reprise d'archives)");
        }
        $this->anneesVerifiees[$annee] = true;
        $this->derniereAnnee = $annee;
    }

    private function enregistrer($l, $importId, $source)
    {
        $enseignant = null;
        if ($l['matricule'] !== null) {
            $enseignant = $this->teachers->find($l['matricule']);
        }
        if ($enseignant === null && $l['nom'] !== null) {
            // anciens classeurs sans matricule : on retrouve l'enseignant par son nom
            $enseignant = $this->teachers->findByName($l['nom']);
        }
        if ($enseignant === null) {
            $this->sansEnseignant++;
        }
        $this->services->insert(array(
            'annee'              => $l['annee'],
            'teacher_id'         => $enseignant ? $enseignant['id'] : null,
            'teacher_nom_source' => $l['nom'],
            'ue'                 => $l['ue'],
            'libelle'            => $l['libelle'],
            'type'               => $l['type'],
            'heures'             => $l['heures'],
            'groupes'            => $l['groupes'],
            'part'               => $l['part'],
            'etd_impose'         => $l['etd_impose'],
            'commentaire'        => $l['commentaire'],
            'source'             => $source,
            'import_id'          => $importId,
        ));
    }

    /** Une valeur HETD saisie en dur (pas une formule) est une valeur imposée. */
    private function etdSaisi($feuille, $col, $ligne)
    {
        $c = $feuille->getCellByColumnAndRow($col, $ligne);
        if ($c->getDataType() == 'f') {
            return null;
        }
        return $this->nombre($this->cellule($feuille, $col, $ligne));
    }

    // Classeur 2018 : feuille "Service", pas de matricule, part en %
    private function importerFormat2018($classeur, $importId, $source, $anneeForcee, $fichier)
    {
        $f = $classeur->getSheetByName('Service');
        $annee = $this->annee($anneeForcee, null, $classeur, $fichier);
        $n = 0;
        $derniere = $f->getHighestRow();
        for ($r = 4; $r <= $derniere; $r++) {
            $nom = $this->cellule($f, 1, $r);
            if ($nom === null) {
                continue;
            }
            if (strtoupper($nom) == 'TOTAL') {
                break;
            }
            $this->enregistrer(array(
                'annee'       => $annee,
                'matricule'   => null,
                'nom'         => $nom,
                'ue'          => $this->cellule($f, 2, $r),
                'libelle'     => $this->cellule($f, 3, $r),
                'type'        => $this->cellule($f, 4, $r),
                'heures'      => $this->nombre($this->cellule($f, 6, $r)),
                'groupes'     => 1, // cf. classeur
                'part'        => $this->nombre($this->cellule($f, 7, $r)),
                'etd_impose'  => $this->etdSaisi($f, 8, $r),
                'commentaire' => $this->cellule($f, 9, $r),
            ), $importId, $source);
            $n++;
        }
        return $n;
    }

    // Classeur 2019 : feuille "SERVICE", code enseignant (parfois absent), heures et groupes séparés
    private function importerFormat2019($classeur, $importId, $source, $anneeForcee, $fichier)
    {
        $f = $classeur->getSheetByName('SERVICE');
        $n = 0;
        $derniere = $f->getHighestRow();
        for ($r = 2; $r <= $derniere; $r++) {
            $nom = $this->cellule($f, 3, $r);
            if ($nom === null) {
                continue;
            }
            if (strtoupper($nom) == 'TOTAL') {
                break;
            }
            $this->enregistrer(array(
                'annee'       => $this->annee($anneeForcee, $this->cellule($f, 1, $r), $classeur, $fichier),
                'matricule'   => $this->cellule($f, 2, $r),
                'nom'         => $nom,
                'ue'          => $this->cellule($f, 4, $r),
                'libelle'     => null,
                'type'        => $this->cellule($f, 5, $r),
                'heures'      => $this->nombre($this->cellule($f, 6, $r)),
                'groupes'     => $this->nombre($this->cellule($f, 7, $r)),
                'part'        => $this->nombre($this->cellule($f, 8, $r)),
                'etd_impose'  => $this->etdSaisi($f, 9, $r),
                'commentaire' => $this->cellule($f, 10, $r),
            ), $importId, $source);
            $n++;
        }
        return $n;
    }

    // Classeur 2023 : feuille "Interventions", titre + ligne d'en-tête à repérer
    private function importerFormat2023($classeur, $importId, $source, $anneeForcee, $fichier)
    {
        $f = $classeur->getSheetByName('Interventions');
        $valeurAnnee = null;
        if (preg_match('#(\d{2,4})\s*[-/]\s*(\d{2,4})\s*$#', (string) $this->cellule($f, 1, 1), $m)) {
            $valeurAnnee = $m[1] . '-' . $m[2];
        }
        $annee = $this->annee($anneeForcee, $valeurAnnee, $classeur, $fichier);
        $entete = null;
        for ($r = 1; $r <= 15; $r++) {
            if ($this->cellule($f, 1, $r) == 'Matricule') {
                $entete = $r;
                break;
            }
        }
        if ($entete === null) {
            throw new \RuntimeException("Ligne d'en-tete introuvable (feuille Interventions)");
        }
        $n = 0;
        $derniere = $f->getHighestRow();
        for ($r = $entete + 1; $r <= $derniere; $r++) {
            $matricule = $this->cellule($f, 1, $r);
            $nom = $this->cellule($f, 2, $r);
            if ($matricule === null && $nom === null) {
                continue;
            }
            if ($nom == 'TOTAL') {
                break;
            }
            $this->enregistrer(array(
                'annee'       => $annee,
                'matricule'   => $matricule,
                'nom'         => $nom,
                'ue'          => $this->cellule($f, 4, $r),
                'libelle'     => $this->cellule($f, 5, $r),
                'type'        => $this->cellule($f, 6, $r),
                'heures'      => $this->nombre($this->cellule($f, 7, $r)),
                'groupes'     => $this->nombre($this->cellule($f, 8, $r)),
                'part'        => $this->nombre($this->cellule($f, 9, $r)),
                'etd_impose'  => $this->etdSaisi($f, 10, $r),
                'commentaire' => $this->cellule($f, 11, $r),
            ), $importId, $source);
            $n++;
        }
        return $n;
    }
}
