<?php
// Import d'un classeur de services
// Usage : php bin/import_excel.php <classeur.xlsx> [--annee=AAAA-AAAA] [--archive]
require __DIR__ . '/_cli.php';

use Serval\Database;
use Serval\Import\ExcelServiceImporter;

list($args, $opts) = serval_options($argv);
if (count($args) < 1) {
    fwrite(STDERR, "Usage : php bin/import_excel.php <classeur.xlsx> [--annee=AAAA-AAAA] [--archive]\n");
    exit(2);
}
$importer = new ExcelServiceImporter(Database::get());
$importer->forcerArchive = isset($opts['archive']);
$n = $importer->importer($args[0], isset($opts['annee']) ? $opts['annee'] : null);
echo $n . " ligne(s) importee(s)";
if ($importer->sansEnseignant > 0) {
    echo " dont " . $importer->sansEnseignant . " sans enseignant";
}
echo "\n";
