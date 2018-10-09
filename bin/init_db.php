<?php
// Création d'une base vide (installation, recette)
// Usage : php bin/init_db.php [--force]
require __DIR__ . '/_cli.php';

use Serval\Database;

list($args, $opts) = serval_options($argv);
$config = Database::config();
$chemin = getenv('SERVAL_DB') ? getenv('SERVAL_DB') : $config['db_path'];
if (file_exists($chemin)) {
    if (!isset($opts['force'])) {
        fwrite(STDERR, "La base existe deja (" . $chemin . "). Option --force pour la recreer (DESTRUCTIF).\n");
        exit(1);
    }
    unlink($chemin);
}
$pdo = new PDO('sqlite:' . $chemin);
$pdo->exec(file_get_contents(__DIR__ . '/../db/schema.sql'));
foreach (glob(__DIR__ . '/../db/migrations/*.sql') as $migration) {
    $pdo->exec(file_get_contents($migration));
    echo "migration " . basename($migration) . "\n";
}
echo "Base creee : " . $chemin . "\n";
