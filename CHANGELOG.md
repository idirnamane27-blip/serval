# Journal des versions

## 2.1 (2022)
- Décharges de service.
- Suppression de LegacyServiceCalculator (prévue).

## 2.0 (2021) — refonte
- Nouveau moteur de calcul `ServiceCalculator`, cache des totaux, tests PHPUnit, Composer, PHP 7.4.
- Reprise des archives 2018-2019 et 2019-2020 dans la base pour l'interface web.
- Tous les calculs passent par le nouveau moteur.

## 1.8 (2020)
- Nouvel export paie au format demandé par la DRH (UTF-8), intégré à `OfficialReportService`.
- La table `heures_comp` n'est plus alimentée.

## 1.6 (2019)
- Clôture des années : les états signés sont figés (table `snapshots`), à la suite de l'incident sur l'état 2017-2018.
- Corrections manuelles validées par la direction (table `adjustments`).
- Passage de PHPExcel à PhpSpreadsheet.

## 1.4 (2018)
- Import des classeurs Excel du secrétariat.

## 1.0 (2016)
- Interface web, base SQLite.

## 0.x (2014-2015)
- Scripts d'export des heures complémentaires vers la paie.
