<?php
// Clôture d'une année universitaire : fige l'état de service (snapshot) avant signature
// Usage : php bin/cloturer_annee.php --annee=AAAA-AAAA
// Voir docs/PROCEDURE_CLOTURE.md
require __DIR__ . '/_cli.php';

use Serval\AcademicYear;
use Serval\Database;
use Serval\Report\OfficialReportService;
use Serval\Repository\SnapshotRepository;
use Serval\Repository\YearRepository;

list($args, $opts) = serval_options($argv);
if (!isset($opts['annee'])) {
    fwrite(STDERR, "Usage : php bin/cloturer_annee.php --annee=AAAA-AAAA\n");
    exit(2);
}
$pdo = Database::get();
$annee = AcademicYear::normalize($opts['annee']);
$annees = new YearRepository($pdo);
$info = $annees->find($annee);
if ($info && $info['cloturee'] == 1) {
    fwrite(STDERR, "Annee " . $annee . " deja cloturee le " . $info['date_cloture'] . "\n");
    exit(1);
}
$service = new OfficialReportService($pdo);
$snapshots = new SnapshotRepository($pdo);
$lignes = $service->lignes($annee);
$pdo->beginTransaction();
foreach ($lignes as $l) {
    $snapshots->insert($l);
}
$annees->cloturer($annee, date('Y-m-d'), 'cli:' . get_current_user());
$pdo->commit();
echo "Annee " . $annee . " cloturee : " . count($lignes) . " etat(s) fige(s)\n";
