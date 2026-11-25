# Étape 3 — Analyse du code de SERVAL

Commit analysé : `12f95a3` — Date : 25 novembre 2026.

J'ai lu les classes, recherché leurs usages avec `rg`, consulté l'historique Git et relancé les tests. Le plan reprend le gabarit `ANALYSE_CODE.md` fourni.

L'objectif est de comprendre le code avant de le modifier. Un code ancien n'est pas forcément inutile, et une différence de résultat n'est pas forcément une erreur.

## 1. Inventaire

« Appelé par » signifie : qui utilise ce code. « Appelle » signifie : ce que ce code utilise.

### Classes de l'application

Les fichiers cités sont les preuves à consulter pour chaque ligne. Les appelants ont été recherchés dans `src/`, `bin/`, `public/` et `tests/`. L'état des tests vient de la lecture des tests existants : ce n'est pas une mesure de couverture.

| Classe (fichier) | Rôle | Appelé par | Appelle | Tests existants | Dernière modification : commit, date, auteur |
|---|---|---|---|---|---|
| [Database](../src/Database.php) | Ouvrir la connexion SQLite | Web et scripts bin/ | PDO, configuration | Non testée par la suite : les tests créent leur propre connexion | d66f54a — 2016-02-15 — Jean-Pierre Roche |
| [AcademicYear](../src/AcademicYear.php) | Reconnaître les écritures des années universitaires | Web, imports, rapports, scripts | Horloge PHP | AcademicYearTest : deux formats et forme de l’année courante ; aussi appelée par ReportServiceTest | d66f54a — 2016-02-15 — Jean-Pierre Roche |
| [ServiceCalculator](../src/Calculator/ServiceCalculator.php) | Calculer les HETD du web | ReportService, ServiceCalculatorTest | Aucune autre classe métier | ServiceCalculatorTest : coefficients, groupes, part, type inconnu, heures négatives ; test du total très limité | ed6c706 — 2021-03-22 — Killian Morvan |
| [LegacyServiceCalculator](../src/Calculator/LegacyServiceCalculator.php) | Calculer les HETD selon les anciennes règles | OfficialReportService | Aucune autre classe métier | Indirectement par ReportServiceTest ; pas de test dédié aux anciennes règles | ed6c706 — 2021-03-22 — Killian Morvan |
| [ReportService](../src/Report/ReportService.php) | Préparer les totaux et les fiches du web | index.php, recalculer_cache.php, tests | AcademicYear, ServiceCalculator, repositories | ReportServiceTest : nombre de résultats et présence de la clé total ; clôture non testée | 58edcbe — 2024-07-05 — Jean-Pierre Roche |
| [OfficialReportService](../src/Report/OfficialReportService.php) | Préparer les états officiels et la paie | Exports, clôture CLI, tests | AcademicYear, LegacyServiceCalculator, repositories | ReportServiceTest : présence de texte dans le CSV et une ligne de paie ; exactitude des montants non vérifiée | 0113d2f — 2025-10-14 — Lucas Perrin |
| [TeacherRepository](../src/Repository/TeacherRepository.php) | Lire et rechercher les enseignants | Web, rapports, imports | PDO | Indirectement par ReportServiceTest ; recherche par nom non testée | d66f54a — 2016-02-15 — Jean-Pierre Roche |
| [ServiceRepository](../src/Repository/ServiceRepository.php) | Lire et insérer les services | Rapports, imports | PDO, AcademicYear | Lectures exécutées par ReportServiceTest ; insertions faites directement en SQL dans ces tests | d66f54a — 2016-02-15 — Jean-Pierre Roche |
| [YearRepository](../src/Repository/YearRepository.php) | Lire, créer et clôturer les années | Web, import Excel, rapports, scripts | PDO | Recherche exécutée par ReportServiceTest ; création et clôture non testées | 0749ff6 — 2019-06-18 — Jean-Pierre Roche |
| [CacheRepository](../src/Repository/CacheRepository.php) | Lire et enregistrer les totaux en cache | ReportService, recalculer_cache.php | PDO, horloge PHP | Lecture et écriture exécutées par ReportServiceTest ; invalidation non vérifiée | ee57185 — 2021-05-10 — Killian Morvan |
| [SnapshotRepository](../src/Repository/SnapshotRepository.php) | Lire et enregistrer les états figés | OfficialReportService, clôture CLI, ancienne clôture web | PDO | Aucun test existant ne passe par une année clôturée ou une insertion de snapshot | 0749ff6 — 2019-06-18 — Jean-Pierre Roche |
| [AdjustmentRepository](../src/Repository/AdjustmentRepository.php) | Lire et enregistrer les ajustements | OfficialReportService, AdjustmentImporter, ancienne clôture web | PDO | Somme exécutée par ReportServiceTest, avec une table vide ; ajustements réels non vérifiés | 0749ff6 — 2019-06-18 — Jean-Pierre Roche |
| [DechargeRepository](../src/Repository/DechargeRepository.php) | Lire les décharges de service | OfficialReportService, ancienne clôture web | PDO | Somme exécutée par ReportServiceTest, avec une table vide ; décharges réelles non vérifiées | fdb9d37 — 2022-03-07 — Jean-Pierre Roche |
| [ImportLogRepository](../src/Repository/ImportLogRepository.php) | Gérer le journal des imports | Importateurs Excel et CSV, page imports | PDO, horloge PHP | Aucun test existant | cb5e2fe — 2018-10-09 — Jean-Pierre Roche |
| [ExcelServiceImporter](../src/Import/ExcelServiceImporter.php) | Lire les différents classeurs historiques | import_excel.php | PhpSpreadsheet, AcademicYear, repositories | Aucun test existant | 6411e41 — 2025-09-26 — Lucas Perrin |
| [CsvLegacyImporter](../src/Import/CsvLegacyImporter.php) | Importer les services CSV | import_csv.php | AcademicYear, repositories, fonctions CSV | Aucun test existant | 31ede58 — 2025-02-10 — Lucas Perrin |
| [AdjustmentImporter](../src/Import/AdjustmentImporter.php) | Importer les ajustements validés | import_ajustements.php | AcademicYear, AdjustmentRepository | Aucun test existant | 0749ff6 — 2019-06-18 — Jean-Pierre Roche |
| [PayrollExporter](../src/Export/PayrollExporter.php) | Ancien export de montants de paie | Aucun appel actif repéré dans le dépôt | PDO, fonctions fichiers et encodage | Aucun test existant | 0e7dd75 — 2016-11-21 — Jean-Pierre Roche |

### Scripts du terminal

Aucun test existant ne lance ces scripts comme un utilisateur le ferait depuis le terminal.

| Fichier dans `bin/` | Rôle | Appelé par | Appelle principalement | Dernière modification |
|---|---|---|---|---|
| [_cli.php](../bin/_cli.php) | Charger le code commun et lire les options | Autres scripts | bootstrap.php, serval_options() | cb5e2fe — 2018-10-09 — Jean-Pierre Roche |
| [init_db.php](../bin/init_db.php) | Créer la base | Opérateur | Database, PDO, schéma et migrations | cb5e2fe — 2018-10-09 — Jean-Pierre Roche |
| [import_excel.php](../bin/import_excel.php) | Importer Excel | Opérateur | ExcelServiceImporter | cb5e2fe — 2018-10-09 — Jean-Pierre Roche |
| [import_csv.php](../bin/import_csv.php) | Importer les services CSV | Opérateur | CsvLegacyImporter | 31ede58 — 2025-02-10 — Lucas Perrin |
| [import_ajustements.php](../bin/import_ajustements.php) | Importer les ajustements | Opérateur | AdjustmentImporter | 0749ff6 — 2019-06-18 — Jean-Pierre Roche |
| [export_officiel.php](../bin/export_officiel.php) | Produire le CSV officiel ou son détail | Opérateur | OfficialReportService | 0749ff6 — 2019-06-18 — Jean-Pierre Roche |
| [export_paie.php](../bin/export_paie.php) | Produire le CSV de paie | Opérateur ou cron documenté | OfficialReportService | d3b0774 — 2020-03-12 — Jean-Pierre Roche |
| [cloturer_annee.php](../bin/cloturer_annee.php) | Figer les états et clôturer une année | Opérateur | OfficialReportService, SnapshotRepository, YearRepository | 0749ff6 — 2019-06-18 — Jean-Pierre Roche |
| [recalculer_cache.php](../bin/recalculer_cache.php) | Remplir le cache ; le vider d’abord avec --tout | Opérateur ou cron documenté | ReportService, CacheRepository, YearRepository | ee57185 — 2021-05-10 — Killian Morvan |

### Pages web

Toutes les pages passent par `public/index.php`, puis un template construit l'affichage. Aucun test existant n'envoie de requête HTTP à ces pages.

| Page ou action | Rôle | Appelle principalement |
|---|---|---|
| Accueil | Lister les années | YearRepository, templates/accueil.php |
| services | Afficher les totaux | ReportService, templates/services.php |
| enseignant | Afficher une fiche individuelle | TeacherRepository, ReportService, templates/enseignant.php |
| ue | Afficher les statistiques par UE | PDO, vue v_etd_par_ue, templates/ue.php |
| imports | Afficher le journal des imports | ImportLogRepository, templates/imports.php |
| ajouter | Enregistrer un service envoyé par formulaire | PDO, AcademicYear ; redirection vers la fiche |

Dernière modification du routeur commun : `58edcbe — 2024-07-05 — Jean-Pierre Roche`. Preuve : [public/index.php](../public/index.php).

### Classes et fichiers de test

| Élément | Rôle et liens |
|---|---|
| AcademicYearTest | Appelé par PHPUnit ; teste AcademicYear |
| ServiceCalculatorTest | Appelé par PHPUnit ; teste ServiceCalculator |
| ReportServiceTest | Appelé par PHPUnit ; utilise TestDatabase, ReportService et OfficialReportService |
| TestDatabase | Crée une base SQLite en mémoire pour ReportServiceTest, à partir du schéma et des migrations |
| tests/bootstrap.php | Charge l'autoload Composer pour PHPUnit |
| src/bootstrap.php | Charge l'autoload et règle l'affichage des erreurs pour le web et les scripts |

## 2. Ce que les commentaires affirment

Un commentaire doit être vérifié dans le code.

| Affirmation | Vérification | Conclusion |
|---|---|---|
| Le nouveau calculateur remplace l'ancien | `ServiceCalculator.php:9` l'annonce, mais `OfficialReportService::ligneEnseignant()` utilise encore l'ancien | Migration incomplète |
| PayrollExporter est indispensable à la paie | `PayrollExporter.php:7` l'annonce ; aucun appel actif trouvé. `bin/export_paie.php` appelle OfficialReportService | Affirmation non confirmée pour l'exploitation actuelle |
| La clôture web est désactivée | `ReportService.php:91` ; appel commenté dans `public/index.php:73` et décision dans `docs/historique/PV_2024-07-04.md` | Confirmé pour le point d'entrée web fourni |
| Le cache est recalculé chaque nuit | Commentaire de CacheRepository et cron documenté ; le script réutilise le cache existant sauf purge avec `--tout` | Il ne garantit pas un recalcul complet sans cette option |

## 3. Code qui semble inutilisé

| Élément | Preuves | Conclusion |
|---|---|---|
| PayrollExporter | Recherche `rg -n 'PayrollExporter' src bin public tests docs` : classe et documentation seulement ; historique `870024c`, `0e7dd75` | Aucun appel actif trouvé dans le dépôt ; usage externe inconnu, donc suppression non décidée |
| ReportService::snapshotAnnee() | Seul appel repéré dans un bloc commenté de `public/index.php:73` ; commit `58edcbe` et PV du 4 juillet 2024 | Ancienne fonction inaccessible depuis le web fourni ; ne pas la réactiver sans décision |
| Database::reset() | Recherche `rg -n 'reset\(' src bin public tests` : définition, sans appel repéré | Usage non trouvé, malgré le commentaire « Utilisé par les tests » |
| LegacyServiceCalculator | Instancié dans OfficialReportService | Toujours utilisé : ce code n'est pas mort |

Une recherche dans le dépôt ne permet pas d'exclure un script externe au projet.

## 4. Code critique

Un code est critique quand une erreur peut modifier un résultat important ou des données.

| Élément | Pourquoi il est critique | Preuve |
|---|---|---|
| Les calculateurs et OfficialReportService | Déterminent les heures affichées, officielles et payées | Méthodes total(), ligneEnseignant(), lignesPaie() |
| Clôture et snapshots | Conservent les états officiels signés | bin/cloturer_annee.php ; OfficialReportService.php:44 ; déclencheurs de protection dans la base |
| Importateurs et recherche d'enseignants | Déterminent à qui les heures sont attribuées | ExcelServiceImporter::enregistrer(), TeacherRepository::findByName() |
| Export paie | Doit respecter le format accepté par la DRH | OfficialReportService::exportPaie() et docs/partenaires/format_export_paie.md |

## 5. Logiques dupliquées ou concurrentes

| Logique | Endroits | Différence ou risque | Exemple simple |
|---|---|---|---|
| Conversion en HETD | ServiceCalculator et LegacyServiceCalculator | TP à 0,67 ou 2/3 ; traitement différent de part = 0 | 30 h de TP, un groupe, part = 1 : 20,10 HETD contre 20 HETD |
| Conversion par UE | Vue SQLite v_etd_par_ue et calculateurs PHP | La vue compte un type inconnu avec un coefficient de 1 ; le nouveau moteur donne zéro | 10 h de SEMINAIRE, un groupe, part = 1 : 10 dans la vue, zéro dans le nouveau moteur |
| Coefficients du nouveau moteur | config/config.php et ServiceCalculator::COEFFICIENTS | Mêmes valeurs aujourd'hui ; risque de divergence lors d'une modification | Changer un seul des deux endroits peut différencier web et appels sans configuration |
| Clôture | Ancienne méthode snapshotAnnee() et bin/cloturer_annee.php | L'une passe par le calcul web, l'autre par le calcul officiel | Le PV de juillet 2024 décrit un écart sur les TP |

Preuves : lecture des deux calculateurs, de `config/config.php`, des deux clôtures et de la définition de la vue dans la copie SQLite. Les exemples sont calculés à partir de ces formules ; ce ne sont pas encore des tests automatisés.

## 6. Anomalies possibles à étudier

| Constat | Si c'est un défaut | Si c'est voulu : hypothèse | Comment trancher | Statut |
|---|---|---|---|---|
| Deux moteurs donnent des totaux différents | Résultats incohérents | Certaines règles historiques doivent peut-être être conservées | Règles datées, archives, ticket 1 et réponse du client | Ouvert |
| La paie utilise des virgules au lieu des points-virgules demandés | Fichier rejeté par la DRH | Une nouvelle convention pourrait exister | Comparer la convention, les références et le ticket partenaire | Écart documentaire établi ; décision à confirmer |
| findByName() retourne le premier nom complet correspondant | Heures attribuées au mauvais homonyme | Les sources supposent peut-être des noms uniques | Vérifier les données et les règles d'identification | Ouvert |
| Le cache existant peut être réutilisé par le script nocturne | Un ancien total peut rester affiché | Le traitement pourrait être prévu seulement pour remplir les valeurs manquantes | Reproduction sur copie et lecture de la procédure d'exploitation | Ouvert |
| ExcelServiceImporter.php:132 compare cloturee avec la chaîne '1' strictement | Le refus d'import peut dépendre du type renvoyé par PDO | Le serveur historique renvoie peut-être toujours une chaîne | Tester un import sur une année clôturée dans l'environnement cible | Ouvert |

## 7. Dépendances à connaître

| Dépendance | Où elle intervient | Si elle change |
|---|---|---|
| Version de PHP et bibliothèques Composer | composer.json ; ancien code de PayrollExporter.php:30 | Certaines syntaxes ou fonctions peuvent devenir incompatibles |
| Fuseau et horloge du serveur | AcademicYear::current(), dateArreteParDefaut(), dates des imports | Année courante ou date d'arrêté différentes près d'une limite de date |
| Chemin de la base | Database::get(), variable SERVAL_DB, config/config.php | Risque d'utiliser la mauvaise base ; toujours choisir une copie pour les essais |
| Schéma complet et déclencheurs SQLite | Repositories et TestDatabase | Sans les migrations ou les protections, le comportement change |
| Noms des feuilles et colonnes Excel | ExcelServiceImporter::importer() et méthodes de lecture | Un classeur peut être refusé ou interprété différemment |

Le fuseau Europe/Paris figure dans la configuration et dans `docs/exploitation/php.ini.prod`, mais aucun appel à `date_default_timezone_set()` n'a été trouvé dans le code fourni.

## 8. Risques techniques

| Constat | Localisation | Gravité estimée | Vérification à faire sur une copie |
|---|---|---|---|
| Année directement ajoutée dans une requête SQL | public/index.php:49, page ue | Élevée : risque d'injection SQL | Envoyer une année contenant une apostrophe et observer la requête ; l'exploitation complète reste à tester |
| Invalidation du cache limitée aux insertions | Déclencheur trg_services_ai_cache de la base | Moyenne : affichage potentiellement périmé | Mettre un total en cache, modifier ou supprimer un service, puis consulter le total |
| Import Excel et CSV sans contrôle de doublon repéré | Méthodes importer() et ServiceRepository::insert() | Élevée : risque de compter deux fois les heures | Importer deux fois le même fichier et comparer les services |
| Journal démarré avant la lecture complète et sans finalisation d'échec visible | ExcelServiceImporter::importer(), ImportLogRepository | Moyenne : diagnostic d'import difficile | Importer un classeur invalide et regarder si le journal reste STARTED |

Ces gravités sont des estimations. Les reproductions proposées ne sont pas encore des diagnostics de tickets ni des corrections.

## 9. Questions ouvertes

- Quelles règles de calcul font foi selon l'année et le document ? Consulter les archives, l'historique et le client à l'étape 5.
- PayrollExporter est-il utilisé par un outil extérieur au dépôt ? À vérifier avec l'exploitation.
- Comment doit fonctionner le réimport d'un fichier corrigé ? À préciser avant de changer les imports.
- Quelle convention de paie est actuellement acceptée par la DRH ? À confirmer avec les éléments du ticket.

Ces questions n'ont pas encore été envoyées au client. Les questions métier retenues seront consignées dans QUESTIONS_CLIENT.md.

## 10. Synthèse

1. Les calculs HETD sont une zone sensible : plusieurs chemins appliquent des règles différentes.
2. Les imports sont sensibles : ils enregistrent les heures et les rattachent aux enseignants.
3. Les clôtures sont sensibles : elles conservent des résultats signés.
4. Les tests actuels vérifient quelques règles du nouveau moteur.
5. Les tests des rapports ne prouvent pas l'exactitude de tous les totaux ni la conformité des exports.
6. Le test d'égalité web/officiel est ignoré ; il ne protège donc pas ce comportement.
7. Il faudra tester en priorité les règles historiques, les imports et les états figés.
8. Le nouveau moteur ne remplace pas encore l'ancien dans tous les traitements.
9. Un commentaire disant qu'un code est critique ou inutilisé ne suffit pas à le prouver.
10. Aucune modification du code de l'application n'a été effectuée pendant cette étape.

Résultat de la suite existante : `php7.4 vendor/bin/phpunit` — 17 tests, 20 assertions, aucun échec, un avertissement sur assertRegExp() et un test ignoré (testWebEgalOfficiel).
