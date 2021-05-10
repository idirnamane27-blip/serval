<?php
// Recalcul complet du cache des totaux affichés par l'interface web (cron, chaque nuit)
// Usage : php bin/recalculer_cache.php [--annee=AAAA-AAAA] [--tout]
require __DIR__ . '/_cli.php';

use Serval\AcademicYear;
use Serval\Database;
use Serval\Report\ReportService;
use Serval\Repository\CacheRepository;
use Serval\Repository\YearRepository;

list($args, $opts) = serval_options($argv);
$pdo = Database::get();
if (isset($opts['tout'])) {
    $cache = new CacheRepository($pdo);
    $cache->clear();
}
$annees = array();
if (isset($opts['annee'])) {
    $annees[] = AcademicYear::normalize($opts['annee']);
} else {
    $repo = new YearRepository($pdo);
    foreach ($repo->all() as $a) {
        $annees[] = $a['code'];
    }
}
$report = new ReportService($pdo, Database::config());
foreach ($annees as $a) {
    echo $a . " : " . count($report->totalsForYear($a)) . " enseignant(s)\n";
}
