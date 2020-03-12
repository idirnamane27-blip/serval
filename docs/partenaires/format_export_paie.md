# Format du fichier « paie des vacataires » attendu par la DRH

Convention d'échange DINFO / DRH — version 2 (mars 2020). Contact : cellule SIRH.

Le fichier est déposé le 1er de chaque mois ; il est lu par l'outil de paie sans intervention humaine.
Tout fichier non conforme est rejeté en bloc.

| Caractéristique       | Valeur attendue                                           |
|-----------------------|-----------------------------------------------------------|
| Encodage              | UTF-8, sans BOM                                           |
| Séparateur de champs  | point-virgule `;`                                         |
| Séparateur décimal    | virgule, deux décimales (`28,50`)                         |
| Fin de ligne          | CR LF                                                     |
| Ligne d'en-tête       | `MATRICULE;NOM;PRENOM;STATUT;ANNEE;HETD;DATE_ARRETE`      |
| Guillemets            | aucun                                                     |

Champs :

- `MATRICULE` : matricule RH, traité comme du texte (les zéros de tête sont significatifs) ;
- `NOM`, `PRENOM` : avec accents ;
- `STATUT` : `VAC` ;
- `ANNEE` : année universitaire `AAAA-AAAA` ;
- `HETD` : cumul des heures équivalent TD de l'année, arrêté à la date indiquée ;
- `DATE_ARRETE` : `JJ/MM/AAAA`, dernier jour du mois écoulé.

L'outil de paie calcule lui-même le montant du mois par différence avec le cumul du mois précédent.

Exemple accepté : `reference/paie_vacataires_2024-2025_arrete_2025-06-30.csv`.
