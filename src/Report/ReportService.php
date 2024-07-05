<?php
declare(strict_types=1);

namespace Serval\Report;

use Serval\AcademicYear;
use Serval\Calculator\ServiceCalculator;
use Serval\Repository\AdjustmentRepository;
use Serval\Repository\CacheRepository;
use Serval\Repository\DechargeRepository;
use Serval\Repository\ServiceRepository;
use Serval\Repository\SnapshotRepository;
use Serval\Repository\TeacherRepository;
use Serval\Repository\YearRepository;

/**
 * Restitutions de l'interface web (totaux par enseignant, détail, alertes).
 */
final class ReportService
{
    private \PDO $pdo;
    private TeacherRepository $teachers;
    private ServiceRepository $services;
    private CacheRepository $cache;
    private ServiceCalculator $calculator;
    private float $seuilAlerte;
    private bool $cacheActif;

    public function __construct(\PDO $pdo, ?array $config = null)
    {
        $config = $config ?? [];
        $this->pdo = $pdo;
        $this->teachers = new TeacherRepository($pdo);
        $this->services = new ServiceRepository($pdo);
        $this->cache = new CacheRepository($pdo);
        $this->calculator = new ServiceCalculator($config['coefficients'] ?? null);
        $this->seuilAlerte = (float) ($config['seuil_alerte'] ?? 0.67);
        $this->cacheActif = (bool) ($config['cache'] ?? true);
    }

    /**
     * Totaux HETD par enseignant pour une année.
     *
     * @return array<int, array{teacher: array, total: float, service_du: float, alerte: bool}>
     */
    public function totalsForYear(string $annee, bool $useCache = true): array
    {
        $annee = AcademicYear::normalize($annee);
        $result = [];
        foreach ($this->teachers->allByName() as $t) {
            $id = (string) $t['id'];
            $total = ($useCache && $this->cacheActif) ? $this->cache->get($annee, $id) : null;
            if ($total === null) {
                $lignes = $this->services->findByTeacherAndYear($id, $annee);
                if (count($lignes) === 0) {
                    continue;
                }
                $total = $this->calculator->total($lignes);
                if ($useCache && $this->cacheActif) {
                    $this->cache->put($annee, $id, $total, count($lignes));
                }
            }
            $du = (float) $t['service_du'];
            $result[] = [
                'teacher'    => $t,
                'total'      => $total,
                'service_du' => $du,
                'alerte'     => $du > 0 && $total < $this->seuilAlerte * $du,
            ];
        }
        return $result;
    }

    /**
     * Détail des lignes d'un enseignant avec le HETD de chaque ligne.
     */
    public function detailForTeacher(string $teacherId, string $annee): array
    {
        $annee = AcademicYear::normalize($annee);
        $lignes = [];
        foreach ($this->services->findByTeacherAndYear($teacherId, $annee) as $l) {
            $l['etd'] = $this->calculator->etdLigne($l);
            $lignes[] = $l;
        }
        return ['lignes' => $lignes, 'total' => $this->calculator->total($lignes)];
    }

    /**
     * Clôture d'une année depuis l'interface web (bouton "Clôturer l'année").
     *
     * @deprecated bouton désactivé depuis le 04/07/2024 - utiliser bin/cloturer_annee.php
     */
    public function snapshotAnnee(string $annee, string $utilisateur): int
    {
        $annee = AcademicYear::normalize($annee);
        $snapshots = new SnapshotRepository($this->pdo);
        $adjustments = new AdjustmentRepository($this->pdo);
        $decharges = new DechargeRepository($this->pdo);
        $n = 0;
        foreach ($this->totalsForYear($annee, false) as $ligne) {
            $t = $ligne['teacher'];
            $ajust = $adjustments->totalFor($t['id'], $annee);
            $dech = $decharges->totalFor($t['id'], $annee);
            $officiel = round($ligne['total'] + $ajust, 2);
            $snapshots->insert([
                'annee' => $annee, 'teacher_id' => $t['id'], 'nom' => $t['nom'], 'prenom' => $t['prenom'],
                'statut' => $t['statut'], 'service_du' => $t['service_du'], 'decharges' => $dech,
                'total_etd' => $ligne['total'], 'ajustements' => $ajust, 'total_officiel' => $officiel,
                'solde' => round($officiel - ((float) $t['service_du'] - $dech), 2), 'observations' => '',
            ]);
            $n++;
        }
        (new YearRepository($this->pdo))->cloturer($annee, date('Y-m-d'), 'web:' . $utilisateur, "Clôture depuis l'interface web");
        return $n;
    }
}
