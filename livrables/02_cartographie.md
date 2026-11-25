# Étape 2 — Cartographie de l'architecture de SERVAL

## 1. Organisation du projet

| Dossier | Responsabilité |
|---|---|
| public/ | Interface web et routage |
| src/ | Code source et logique métier |
| config/ | Configuration de l'application |
| templates/ | Affichage des pages |
| bin/ | Scripts d'import, d'export et de maintenance |
| db/ | Structure de la base de données |
| data/ | Données de l'application |
| tests/ | Tests automatisés |
| vendor/ | Dépendances externes |

### Points d'entrée

Un point d'entrée est le fichier par lequel on lance une fonction de l'application.

| Fichier | Comment il est lancé | Rôle |
|---|---|---|
| `public/index.php` | Navigateur web | Afficher les années, les services, les fiches enseignants, les statistiques et le journal des imports ; permettre l'ajout d'un service |
| `bin/import_excel.php` | Terminal | Importer un classeur Excel |
| `bin/import_csv.php` | Terminal | Importer des services depuis un CSV |
| `bin/import_ajustements.php` | Terminal | Importer les corrections validées par la direction |
| `bin/export_officiel.php` | Terminal | Produire l'état officiel ou le détail des services |
| `bin/export_paie.php` | Terminal ou cron selon la documentation | Produire le fichier de paie des vacataires |
| `bin/cloturer_annee.php` | Terminal | Figer les états officiels et clôturer l'année |
| `bin/recalculer_cache.php` | Terminal ou cron selon la documentation | Compléter le cache des totaux ; l'option `--tout` le vide d'abord |
| `bin/init_db.php` | Terminal | Créer une base de données |

Cron est l'outil qui lance des commandes automatiquement à des horaires définis. Les tâches prévues sont présentées dans la section 8.

Le schéma suivant présente les calculs des services à l'écran et dans les exports. Les imports ont leur propre chemin, présenté dans la section 7.

## 2. Architecture générale

```text
Interface web (index.php)       Scripts CLI (bin/)
           |                           |
           v                           v
     ReportService            OfficialReportService
           |                           |
           v                           v
    ServiceCalculator         LegacyServiceCalculator
           |                           |
           +------------+--------------+
                        |
                  Repositories
                        |
                      SQLite
```

Les repositories permettent d'accéder aux données SQLite. Les services utilisent ces données pour effectuer les traitements métier.

Les templates permettent de présenter les résultats dans l'interface web.

## 3. Principales classes

| Classe | Responsabilité |
|---|---|
| Database | Connexion à SQLite |
| AcademicYear | Gestion des années universitaires |
| ServiceCalculator | Nouveau moteur de calcul HETD |
| LegacyServiceCalculator | Ancien moteur de calcul HETD |
| ReportService | Calcul et affichage des services sur le web |
| OfficialReportService | États officiels et export paie |
| CacheRepository | Stockage des totaux déjà calculés |
| SnapshotRepository | Accès aux états figés des années clôturées |

## 4. Règles métier

Les heures d'enseignement sont converties en heures équivalent TD (HETD).

Formule principale :

HETD = heures × groupes × coefficient × part

| Type | Coefficient du nouveau moteur |
|---|---:|
| CM | 1,5 |
| TD | 1 |
| TP | 0,67 |
| PROJET | 0,5 |

## 5. Comparaison des moteurs de calcul

| Règle | Ancien moteur | Nouveau moteur |
|---|---|---|
| Coefficient TP | 2/3 | 0,67 |
| Part égale à zéro | 100 % | 0 % |
| Heures égales à 999 | À recalculer | Calcul normal |
| Arrondi | Aucun | Deux décimales par ligne |
| Utilisation | Exports officiels et paie | Interface web |

**Précision :** l'ancien moteur n'arrondit pas chaque ligne, mais les totaux officiels et de paie sont ensuite arrondis à deux décimales.

## 6. Structure de la base de données SQLite

J'ai analysé la structure de la base avec la commande `sqlite3 var/serval.sqlite ".schema"`.

### Tables principales

| Table | Responsabilité |
|---|---|
| teachers | Informations des enseignants |
| services | Heures d'enseignement |
| import_log | Journal des imports |
| annees | Gestion des années universitaires |
| snapshots | Conservation des états officiels figés |
| adjustments | Ajustements manuels des heures |
| cache_totaux | Stockage des totaux calculés |
| decharges | Décharges de service |

### Vue SQL

La vue `v_etd_par_ue` calcule les statistiques et les HETD par unité d'enseignement. Elle possède ses propres règles de calcul.

### Déclencheurs SQL (triggers)

| Déclencheur | Responsabilité |
|---|---|
| trg_services_ai_cache | Invalide le cache lors de l'ajout d'un service |
| trg_snapshots_bu | Interdit la modification des snapshots |
| trg_snapshots_bd | Interdit la suppression des snapshots |

### Index de la base

Un index aide la base à retrouver les données plus rapidement, comme l'index d'un livre.

| Index | Utilité |
|---|---|
| `idx_services_annee_enseignant` | Retrouver les services d'un enseignant pour une année donnée |
| Index automatiques des tables `teachers`, `annees` et `cache_totaux` | Associés aux identifiants uniques des enseignants, des années et des entrées du cache |

Ces index ont été vérifiés dans la structure de la copie de la base SQLite.

## 7. Flux de données

### 7.1. Consultation des services sur le web

`Utilisateur → index.php → ReportService → Repositories / SQLite → ServiceCalculator → Template → Affichage`

L'utilisateur sélectionne une année. L'application récupère les services des enseignants, utilise le cache ou calcule leurs HETD, puis affiche les résultats.

### 7.2. Export de l'état officiel

`bin/export_officiel.php → OfficialReportService → Repositories / SQLite → Calcul ou snapshots → CSV`

Pour une année ouverte, l'application utilise l'ancien moteur de calcul et prend en compte les ajustements et les décharges.

Pour une année clôturée, elle récupère les états officiels conservés dans les snapshots.

### 7.3. Import d'un classeur Excel

`Classeur Excel → bin/import_excel.php → ExcelServiceImporter → ServiceRepository → SQLite`

L'importateur utilise la bibliothèque PhpSpreadsheet pour lire les classeurs.

| Période | Nom de la feuille Excel |
|---|---|
| 2018 | Service |
| 2019 | SERVICE |
| 2023 | Interventions |
| 2024 et suivantes | Services |

L'importateur identifie le format, normalise l'année universitaire, recherche les enseignants par matricule puis par nom et enregistre les services dans la base.

Une transaction permet d'annuler les insertions en cas d'exception pendant l'import.

## 8. Tâches planifiées

J'ai consulté le fichier `docs/exploitation/crontab.txt` pour identifier les traitements automatiques du serveur.

| Traitement | Fréquence | Responsabilité |
|---|---|---|
| recalculer_cache.php | Chaque jour à 00h15 | Recalculer le cache des totaux |
| export_paie.php | Le premier du mois à 00h05 | Générer le fichier destiné à la DRH |
| Sauvegarde SQLite | Chaque dimanche à 02h30 | Sauvegarder la base de données |

Ces traitements sont exécutés automatiquement par cron.

**Précision :** ces tâches sont décrites dans le relevé du serveur de 2024. Leur activation sur ma machine n'a pas été vérifiée.

## 9. Fichiers échangés et partenaires

| Fichier | Utilisateur ou partenaire |
|---|---|
| Classeurs Excel | Secrétariat |
| Fichiers CSV importés | Autres composantes |
| États officiels CSV | Direction du département |
| Exports paie CSV | Service chargé de la paie |

### Sens et formats des échanges

| Échange | Sens | Format |
|---|---|---|
| Services d'enseignement | Secrétariat → SERVAL | Excel `.xlsx` |
| Services des autres composantes | Autres composantes → SERVAL | CSV séparé par des points-virgules |
| Ajustements validés | Direction → SERVAL | CSV séparé par des points-virgules |
| État officiel | SERVAL → direction | CSV séparé par des points-virgules |
| Export paie actuel | SERVAL → service de paie | CSV séparé par des virgules |

La convention de la DRH demande des points-virgules pour l'export paie : le format actuel présente donc un écart avec cette documentation.

## 10. Observations techniques

| Observation | Risque |
|---|---|
| Deux moteurs de calcul PHP différents | Résultats incohérents |
| Troisième chemin de calcul dans la vue SQL | Écarts supplémentaires entre les résultats |
| Concaténation de paramètres dans certaines requêtes SQL | Injection SQL |
| Coefficients définis à plusieurs endroits | Difficulté de maintenance |
| Présence de code déprécié | Incompatibilité avec les futures versions PHP |
| Utilisation du cache | Résultats potentiellement obsolètes |
| Déclencheur d'invalidation limité aux insertions | Risque de cache obsolète après une modification ou une suppression |
| Plusieurs formats Excel historiques | Complexité de maintenance des imports |
| Fuseau horaire configuré mais application non confirmée | Incohérences liées aux dates |

Ces observations constituent des pistes d'investigation qui devront être vérifiées avant toute correction.


## 11. Comparaison avec la documentation existante

J'ai comparé le fonctionnement réel de l'application avec les fichiers `docs/ARCHITECTURE.md` et `docs/partenaires/format_export_paie.md`.

| Documentation | Réalité observée |
|---|---|
| Tous les calculs passent par ServiceCalculator | Deux moteurs PHP sont toujours utilisés |
| LegacyServiceCalculator doit être supprimé | Il est toujours utilisé par OfficialReportService |
| Le cache est invalidé par l'importateur | Un déclencheur SQL assure également l'invalidation lors des insertions |
| La recherche par nom doit être remplacée | Elle est toujours présente dans ExcelServiceImporter |
| La DRH attend un CSV séparé par des points-virgules | L'export paie actuel utilise des virgules |

La documentation d'architecture date de la refonte de 2021 et ne correspond plus entièrement au fonctionnement actuel de SERVAL.

J'ai donc constaté que la documentation doit être vérifiée à partir du code source et des résultats réels de l'application.

### Où vérifier ces constats ?

| Constat | Fichier ou méthode à consulter |
|---|---|
| Le web utilise le nouveau moteur | `src/Report/ReportService.php`, méthode `totalsForYear()` |
| Les calculs officiels utilisent encore l'ancien moteur | `src/Report/OfficialReportService.php`, méthode `ligneEnseignant()` |
| Un ajout de service invalide le cache | Déclencheur `trg_services_ai_cache` dans la base SQLite |
| L'import Excel recherche encore un enseignant par son nom | `src/Import/ExcelServiceImporter.php`, méthode `enregistrer()` |
| L'export paie utilise des virgules | `src/Report/OfficialReportService.php`, méthode `exportPaie()` |
| La DRH demande des points-virgules | `docs/partenaires/format_export_paie.md` |

Ces références permettent de retrouver les preuves pendant la présentation.

## 12. Conclusion

J'ai analysé l'architecture de SERVAL, ses principaux composants, sa base de données et ses différents flux de traitement.

Cette cartographie m'a permis de comprendre le fonctionnement de l'interface web, des imports, des exports et des tâches automatiques.

J'ai également identifié plusieurs risques techniques, notamment la duplication des règles de calcul des HETD dans deux moteurs PHP et une vue SQL.

Ces risques seront étudiés et vérifiés dans les prochaines étapes du projet.

Aucune modification du code source n'a été effectuée pendant cette étape.
