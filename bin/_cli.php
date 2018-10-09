<?php
// Outils communs aux scripts en ligne de commande
require __DIR__ . '/../src/bootstrap.php';

/** Sépare les arguments (fichiers) des options --cle=valeur / --drapeau. */
function serval_options($argv)
{
    $args = array();
    $opts = array();
    foreach (array_slice($argv, 1) as $a) {
        if (substr($a, 0, 2) == '--') {
            $kv = explode('=', substr($a, 2), 2);
            $opts[$kv[0]] = isset($kv[1]) ? $kv[1] : true;
        } else {
            $args[] = $a;
        }
    }
    return array($args, $opts);
}
