# Étape 1 — Installation du legacy SERVAL

## 1. Environnement de travail

J'ai installé et configuré les outils nécessaires au fonctionnement de SERVAL.

| Outil | Version |
|---|---|
| Ubuntu | 24.04.2 LTS |
| PHP | 7.4.33 |
| Composer | 2.2.33 |
| SQLite | 3.45.1 |
| Git | 2.43.0 |
| PHPUnit | 9.6.39 |

## 2. Installation

### Installation des dépendances

Composer permet d'installer les bibliothèques nécessaires au fonctionnement de l'application.

```bash
php7.4 ~/outils/composer.phar install
```

**Résultat :** les dépendances sont installées dans le dossier `vendor/`.

### Copie de la base de données

```bash
mkdir -p var
cp data/serval.sqlite var/serval.sqlite
```

**Objectif :** travailler sur une copie afin de préserver la base originale.

## 3. Vérification du fonctionnement

### Test 1 — Lancement de l'application

**Objectif :** vérifier que l'application web démarre correctement et permet de consulter les données universitaires.

```bash
SERVAL_DB="$PWD/var/serval.sqlite" php7.4 -S localhost:8080 -t public
```

**Résultat :** l'application est accessible sur `localhost:8080`.

**Observation :** huit années universitaires sont affichées, dont deux ouvertes et six clôturées.

### Test 2 — Tests automatisés

**Objectif :** utiliser les tests existants pour vérifier le comportement de certaines fonctionnalités du code.

```bash
SERVAL_DB="$PWD/var/serval.sqlite" php7.4 vendor/bin/phpunit
```

| Indicateur | Résultat |
|---|---:|
| Tests | 17 |
| Assertions | 20 |
| Échecs | 0 |
| Erreurs | 0 |
| Avertissements | 1 |
| Tests ignorés | 1 |

**Observation :** la méthode `assertRegExp()` est dépréciée. Un test est ignoré pour une raison qui reste à identifier.

### Test 3 — Export officiel

L'export officiel permet de générer un état récapitulatif des services d'enseignement : heures dues, heures effectuées, décharges, ajustements et soldes des enseignants.

**Objectif :** vérifier que SERVAL peut générer cet état pour l'année universitaire 2024-2025.

```bash
SERVAL_DB="$PWD/var/serval.sqlite" php7.4 bin/export_officiel.php --annee=2024-2025 > var/etat_officiel.csv
```

**Résultat :** le fichier `etat_officiel.csv` est généré avec succès.

**Observation :** les données sont séparées par des points-virgules.

### Test 4 — Export paie

L'export paie permet de produire les données relatives aux heures d'enseignement des vacataires, nécessaires au traitement de leur rémunération.

**Objectif :** vérifier la génération de l'export paie pour l'année 2024-2025, avec une date d'arrêté fixée au 30 juin 2025.

```bash
SERVAL_DB="$PWD/var/serval.sqlite" php7.4 bin/export_paie.php --annee=2024-2025 --arrete=2025-06-30 > var/paie.csv
```

**Résultat :** le fichier `paie.csv` est généré avec quatre lignes de données.

**Observation :** les données sont séparées par des virgules.

## 4. Écarts avec la documentation initiale

| Documentation initiale | Installation réalisée |
|---|---|
| Debian 10 | Ubuntu 24.04 |
| PHP 7.3 | PHP 7.4 |
| Composer système | Composer autonome compatible |
| Installation sans dépendances de développement | Installation avec PHPUnit |
| Initialisation de la base | Utilisation d'une copie |

## 5. Conclusion

SERVAL fonctionne sous PHP 7.4. Les tests existants s'exécutent sans échec signalé et les deux exports génèrent des fichiers CSV.

L'exactitude des calculs des exports reste à vérifier.

L'historique Git est conservé et aucune modification du code source n'a été effectuée.