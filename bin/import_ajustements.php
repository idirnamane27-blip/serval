<?php
// Import des corrections manuelles validées par la direction
// Usage : php bin/import_ajustements.php <ajustements.csv>
require __DIR__ . '/_cli.php';

use Serval\Database;
use Serval\Import\AdjustmentImporter;

list($args, $opts) = serval_options($argv);
if (count($args) < 1) {
    fwrite(STDERR, "Usage : php bin/import_ajustements.php <ajustements.csv>\n");
    exit(2);
}
$importer = new AdjustmentImporter(Database::get());
echo $importer->importer($args[0]) . " ajustement(s) importe(s)\n";
