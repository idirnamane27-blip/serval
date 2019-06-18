<?php
namespace Serval\Import;

use Serval\AcademicYear;
use Serval\Repository\AdjustmentRepository;

/**
 * Import des corrections manuelles validées (fichier transmis par la direction).
 * Colonnes : annee;matricule;heures_etd;motif;valide_par;date_validation
 */
class AdjustmentImporter
{
    private $adjustments;

    public function __construct(\PDO $pdo)
    {
        $this->adjustments = new AdjustmentRepository($pdo);
    }

    public function importer($fichier)
    {
        $fh = fopen($fichier, 'r');
        if (!$fh) {
            throw new \RuntimeException('Fichier illisible : ' . $fichier);
        }
        fgetcsv($fh, 0, ';'); // en-tête
        $n = 0;
        while (($c = fgetcsv($fh, 0, ';')) !== false) {
            if (count($c) < 6) {
                continue;
            }
            $this->adjustments->insert(array(
                'annee'           => AcademicYear::normalize($c[0]),
                'teacher_id'      => $c[1],
                'heures_etd'      => (float) str_replace(',', '.', $c[2]),
                'motif'           => $c[3],
                'valide_par'      => $c[4],
                'date_validation' => $c[5],
                'origine'         => 'MANUEL',
            ));
            $n++;
        }
        fclose($fh);
        return $n;
    }
}
