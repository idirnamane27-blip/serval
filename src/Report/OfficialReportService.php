<?php
namespace Serval\Report;

use Serval\AcademicYear;
use Serval\Calculator\ServiceCalculator;
use Serval\Repository\AdjustmentRepository;
use Serval\Repository\DechargeRepository;
use Serval\Repository\ServiceRepository;
use Serval\Repository\SnapshotRepository;
use Serval\Repository\TeacherRepository;
use Serval\Repository\YearRepository;

/**
 * États de service officiels (direction, RH) et export vers l'outil de paie.
 */
class OfficialReportService
{
    private $pdo;
    private $teachers;
    private $services;
    private $snapshots;
    private $adjustments;
    private $annees;
    private $decharges;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->teachers = new TeacherRepository($pdo);
        $this->services = new ServiceRepository($pdo);
        $this->snapshots = new SnapshotRepository($pdo);
        $this->adjustments = new AdjustmentRepository($pdo);
        $this->annees = new YearRepository($pdo);
        $this->decharges = new DechargeRepository($pdo);
    }

    /**
     * Lignes de l'état de service d'une année (une ligne par enseignant).
     */
    public function lignes($annee)
    {
        $annee = AcademicYear::normalize($annee);
        $info = $this->annees->find($annee);
        if ($info && $info['cloturee'] == 1) {
            return $this->snapshots->findByYear($annee);
        }
        $out = array();
        foreach ($this->teachers->allByName() as $t) {
            $lignes = $this->services->findByTeacherAndYear($t['id'], $annee);
            $l = $this->ligneEnseignant($t, $annee, $lignes);
            if ($l !== null) {
                $out[] = $l;
            }
        }
        return $out;
    }

    private function ligneEnseignant($t, $annee, $lignes)
    {
        $ajust = $this->adjustments->totalFor($t['id'], $annee);
        if (count($lignes) == 0 && $ajust == 0) {
            return null;
        }
        $calc = new ServiceCalculator();
        $total = $calc->total($lignes);
        $decharges = $this->decharges->totalFor($t['id'], $annee);
        $officiel = round($total + $ajust, 2);
        $obs = '';
        if ($calc->aRecalculer > 0) {
            $obs = 'A RECALCULER (' . $calc->aRecalculer . ' ligne(s) 999)';
        }
        return array(
            'annee'          => $annee,
            'teacher_id'     => $t['id'],
            'nom'            => $t['nom'],
            'prenom'         => $t['prenom'],
            'statut'         => $t['statut'],
            'service_du'     => $t['service_du'],
            'decharges'      => $decharges,
            'total_etd'      => round($total, 2),
            'ajustements'    => $ajust,
            'total_officiel' => $officiel,
            'solde'          => round($officiel - ($t['service_du'] - $decharges), 2),
            'observations'   => $obs,
        );
    }

    /** État de service au format CSV (séparateur ;, virgule décimale). */
    public function toCsv($annee)
    {
        $csv = "annee;matricule;nom;prenom;statut;service_du;decharges;total_etd;ajustements;total_officiel;solde;observations\n";
        foreach ($this->lignes($annee) as $l) {
            $csv .= implode(';', array(
                $l['annee'], $l['teacher_id'], $l['nom'], $l['prenom'], $l['statut'],
                $this->nb($l['service_du']), $this->nb($l['decharges']), $this->nb($l['total_etd']),
                $this->nb($l['ajustements']), $this->nb($l['total_officiel']), $this->nb($l['solde']),
                $l['observations'],
            )) . "\n";
        }
        return $csv;
    }

    /** Détail ligne à ligne (fiches individuelles), HETD non arrondies. */
    public function detailCsv($annee)
    {
        $annee = AcademicYear::normalize($annee);
        $calc = new ServiceCalculator();
        $csv = "annee;matricule;nom;prenom;ue;type;heures;groupes;part;etd_impose;hetd\n";
        foreach ($this->teachers->allByName() as $t) {
            foreach ($this->services->findByTeacherAndYear($t['id'], $annee) as $l) {
                $csv .= implode(';', array(
                    $annee, $t['id'], $t['nom'], $t['prenom'], $l['ue'], $l['type'], $l['heures'], $l['groupes'],
                    $l['part'], $l['etd_impose'], number_format($calc->calculerLigne($l), 4, ',', ''),
                )) . "\n";
            }
        }
        return $csv;
    }

    private function nb($v)
    {
        return number_format((float) $v, 2, ',', '');
    }

    /** Date d'arrêté par défaut de l'export paie : dernier jour du mois précédent. */
    public function dateArreteParDefaut()
    {
        return date('Y-m-d', strtotime('last day of previous month'));
    }

    /**
     * Heures à payer aux vacataires : lignes saisies jusqu'à la date d'arrêté.
     */
    public function lignesPaie($annee, $arrete)
    {
        $annee = AcademicYear::normalize($annee);
        $out = array();
        foreach ($this->teachers->allByName() as $t) {
            if ($t['statut'] != 'VAC') {
                continue;
            }
            $lignes = array();
            foreach ($this->services->findByTeacherAndYear($t['id'], $annee) as $l) {
                if (substr($l['created_at'], 0, 10) <= $arrete) {
                    $lignes[] = $l;
                }
            }
            $calc = new ServiceCalculator();
            $hetd = round($calc->total($lignes) + $this->adjustments->totalFor($t['id'], $annee), 2);
            if ($hetd == 0) {
                continue;
            }
            $out[] = array('teacher_id' => $t['id'], 'nom' => $t['nom'], 'prenom' => $t['prenom'], 'statut' => $t['statut'], 'annee' => $annee, 'hetd' => $hetd);
        }
        return $out;
    }
    /**
     * Export pour l'outil de paie des vacataires (DRH).
     * Format de la convention d'échange v2 : voir docs/partenaires/format_export_paie.md
     */
    public function exportPaie($annee, $arrete, $fh)
    {
        fwrite($fh, "MATRICULE;NOM;PRENOM;STATUT;ANNEE;HETD;DATE_ARRETE\r\n");
        $n = 0;
        foreach ($this->lignesPaie($annee, $arrete) as $l) {
            fwrite($fh, implode(';', array(
                $l['teacher_id'], $l['nom'], $l['prenom'], $l['statut'], $l['annee'],
                number_format($l['hetd'], 2, ',', ''), date('d/m/Y', strtotime($arrete)),
            )) . "\r\n");
            $n++;
        }
        return $n;
    }
}
