# Architecture de SERVAL (refonte 2021)

    +-----------------+        +------------------+        +--------------------+        +----------+
    | Interface web   | -----> |                  |        |                    |        |          |
    | public/index.php|        |  ReportService   | -----> | ServiceCalculator  | -----> |  SQLite  |
    +-----------------+        |                  |        |                    |        |          |
    | Scripts bin/    | -----> |                  |        |                    |        |          |
    +-----------------+        +------------------+        +--------------------+        +----------+

Depuis la version 2.0, tous les calculs passent par `ServiceCalculator`. `LegacyServiceCalculator`
n'est conservé que pour comparaison pendant la migration et sera supprimé.

## Couches

- `src/Repository` : accès aux tables (un dépôt par table).
- `src/Calculator` : calcul des HETD.
- `src/Report` : restitutions (totaux, états, exports).
- `src/Import` : import des classeurs et des fichiers CSV.

## Cache

Les totaux par enseignant et par année sont stockés dans la table `cache_totaux`.
L'importeur invalide le cache de l'année importée ; un traitement nocturne recalcule l'ensemble.

## Base de données

Voir `db/schema.sql` (schéma complet) et `db/migrations/`.

## Reste à faire (juin 2021)

- finir la migration des exports ;
- supprimer `LegacyServiceCalculator` et `src/Export/PayrollExporter.php` ;
- remplacer le fallback par nom par une table de correspondance.
