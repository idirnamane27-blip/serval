<?php
// Export mensuel vers l'outil de paie des vacataires (DRH)
// Usage : php bin/export_paie.php [--annee=AAAA-AAAA] [--arrete=AAAA-MM-JJ]
// Lancé par cron le 1er de chaque mois (cf. docs/exploitation/crontab.txt)
require __DIR__ . '/_cli.php';

use Serval\AcademicYear;
use Serval\Database;
use Serval\Report\OfficialReportService;

list($args, $opts) = serval_options($argv);
$service = new OfficialReportService(Database::get());
$annee = isset($opts['annee']) ? $opts['annee'] : AcademicYear::current();
$arrete = isset($opts['arrete']) ? $opts['arrete'] : $service->dateArreteParDefaut();
$n = $service->exportPaie($annee, $arrete, STDOUT);
fwrite(STDERR, "Export paie " . AcademicYear::normalize($annee) . " arrete au " . $arrete . " : " . $n . " ligne(s)\n");
