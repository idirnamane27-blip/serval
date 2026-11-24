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

## 6. Observations techniques

| Observation | Risque |
|---|---|
| Deux moteurs de calcul différents | Résultats incohérents |
| Concaténation de paramètres dans certaines requêtes SQL | Injection SQL |
| Coefficients définis à plusieurs endroits | Difficulté de maintenance |
| Présence de code déprécié | Incompatibilité avec les futures versions PHP |
| Utilisation du cache | Résultats potentiellement obsolètes |
| Fuseau horaire configuré mais application non confirmée | Incohérences liées aux dates |

## 7. Conclusion

SERVAL possède une séparation partielle entre l'interface, les traitements métier et l'accès aux données.

L'analyse a identifié plusieurs points nécessitant une investigation, notamment la coexistence de deux moteurs de calcul.

Les anomalies seront étudiées et vérifiées lors du traitement des tickets.

Aucune modification du code source n'a été effectuée pendant cette étape.