<?php
// Configuration SERVAL
// Rappel : le fuseau horaire est celui du serveur (cf. php.ini)
return array(
    'db_path'       => __DIR__ . '/../data/serval.sqlite',
    'timezone'      => 'Europe/Paris',
    'etablissement' => 'Université de Rochebrune - Département Informatique',
    // coefficients HETD (refonte 2021)
    'coefficients'  => array(
        'CM'     => 1.5,
        'TD'     => 1.0,
        'TP'     => 0.67,
        'PROJET' => 0.5,
    ),
    // alerte "sous-service" quand le total est inférieur à 67 % du service dû
    'seuil_alerte'  => 0.67,
    'cache'         => true,
);
