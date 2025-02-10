<?php
// Import d'un fichier CSV (vacataires, compléments des autres composantes)
// Usage : php bin/import_csv.php <fichier.csv> [--annee=AAAA-AAAA]
require __DIR__ . '/_cli.php';

use Serval\Database;
use Serval\Import\CsvLegacyImporter;

list($args, $opts) = serval_options($argv);
if (count($args) < 1) {
    fwrite(STDERR, "Usage : php bin/import_csv.php <fichier.csv> [--annee=AAAA-AAAA]\n");
    exit(2);
}
$importer = new CsvLegacyImporter(Database::get());
$n = $importer->importer($args[0], isset($opts['annee']) ? $opts['annee'] : null);
echo $n . " ligne(s) importee(s)\n";
