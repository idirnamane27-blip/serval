<?php
// Chargement commun (interface web + scripts bin/)
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '1');

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die("Dependances absentes : lancer 'composer install' a la racine du projet.\n");
}
require __DIR__ . '/../vendor/autoload.php';
