<?php
// État de service officiel d'une année (CSV sur la sortie standard)
// Usage : php bin/export_officiel.php [--annee=AAAA-AAAA] [--detail]
require __DIR__ . '/_cli.php';

use Serval\AcademicYear;
use Serval\Database;
use Serval\Report\OfficialReportService;

list($args, $opts) = serval_options($argv);
$annee = isset($opts['annee']) ? $opts['annee'] : AcademicYear::current();
$service = new OfficialReportService(Database::get());
echo isset($opts['detail']) ? $service->detailCsv($annee) : $service->toCsv($annee);
