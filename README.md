# SERVAL — Services d'enseignement

SERVAL (« SERVices d'enseignement : Agrégation et vaLidation ») est l'application de gestion des
services d'enseignement du département Informatique de l'Université de Rochebrune.

Version 2.4 — document mis à jour lors de la refonte de 2021.

## À quoi sert l'application ?

Pour chaque enseignant et chaque année universitaire, SERVAL calcule le nombre d'heures
équivalent TD (HETD) à partir des interventions saisies par le secrétariat pédagogique :

- import des classeurs Excel du secrétariat (`bin/import_excel.php`) ;
- import des fichiers CSV des autres composantes (`bin/import_csv.php`) ;
- consultation par l'interface web (`public/index.php`) ;
- édition de l'état de service officiel (`bin/export_officiel.php`) ;
- export mensuel vers l'outil de paie des vacataires (`bin/export_paie.php`) ;
- clôture de l'année (`bin/cloturer_annee.php`).

Statuts gérés : enseignants-chercheurs (EC, 192 h), PRAG (384 h), ATER (192 h ou 96 h), vacataires (VAC).

## Règles de calcul

| Type   | Coefficient HETD |
|--------|------------------|
| CM     | 1,5              |
| TD     | 1                |
| TP     | 0,67             |
| PROJET | 0,5              |

- TP = 0,67 est la règle officielle (voir l'onglet `Parametres` des classeurs).
- Les types d'intervention non reconnus sont rejetés.
- Le nombre d'heures est toujours positif.
- Les responsabilités (type `RESP`) portent directement une valeur HETD.

Le calcul est réalisé par la classe `ServiceCalculator`, qui est le calculateur officiel utilisé
partout (interface web, états officiels, export paie). L'ancienne classe `LegacyServiceCalculator`
est dépréciée et sera supprimée.

## Données

- Un enseignant est identifié par son matricule (`E001`, `E002`...). Dans les anciens classeurs
  sans matricule, on utilise le nom complet : le couple NOM Prénom est unique dans le département.
- Les années universitaires sont toujours écrites `AAAA-AAAA` (ex. `2024-2025`).
- Les fichiers CSV échangés sont en UTF-8.
- La base SQLite se trouve dans `data/serval.sqlite`. Son schéma est décrit dans `db/schema.sql`.
- Les totaux affichés par l'interface web sont mis en cache ; le cache est invalidé à chaque import
  et recalculé chaque nuit.

## Installation

Voir `INSTALL.txt`. En résumé :

    composer install
    php -S localhost:8080 -t public

## Utilisation

    php bin/import_excel.php archives/excel/services_2025-2026.xlsx
    php bin/import_csv.php archives/csv/complements_2025-2026.csv --annee=2025-2026
    php bin/export_officiel.php --annee=2024-2025 > etat_2024-2025.csv
    php bin/export_paie.php --annee=2024-2025 --arrete=2025-06-30 > paie.csv
    php bin/cloturer_annee.php --annee=2024-2025

## Tests

    vendor/bin/phpunit

La refonte de 2021 a apporté une suite de tests unitaires (couverture supérieure à 80 % sur le calcul).

## Organisation du dépôt

    bin/        scripts en ligne de commande
    config/     configuration
    db/         schéma de la base et migrations
    docs/       documentation (architecture, règles métier, procédures)
    public/     interface web
    src/        classes PHP (namespace Serval)
    templates/  gabarits HTML
    tests/      tests PHPUnit
    archives/   classeurs Excel et fichiers CSV reçus du secrétariat
    reference/  états officiels archivés
    data/       base SQLite
