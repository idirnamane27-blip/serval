# Procédure de clôture d'une année universitaire

(mise en place en 2019 après l'incident sur l'état 2017-2018, modifié après signature)

1. Le secrétariat vérifie les services avec chaque enseignant (fiches individuelles).
2. La direction valide les corrections manuelles (table `adjustments`) : chaque correction porte un
   motif, le nom du valideur et la date de validation.
3. L'administrateur lance `php bin/cloturer_annee.php --annee=AAAA-AAAA`.
   L'état de service de chaque enseignant est figé dans la table `snapshots`.
4. L'état est édité (`bin/export_officiel.php`), signé par le directeur du département et transmis
   à la DRH. Une copie est archivée dans `reference/`.

Après la clôture :

- l'état signé fait foi : `bin/export_officiel.php` relit le snapshot, il ne recalcule rien ;
- un état signé ne se modifie pas. Toute contestation ultérieure passe par un état rectificatif
  validé par la direction, jamais par une modification des données de l'année ;
- les lignes de détail peuvent encore évoluer (reprise d'archives, corrections d'affichage) sans
  effet sur l'état signé.
