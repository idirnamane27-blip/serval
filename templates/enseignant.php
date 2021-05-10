<h2><?php echo $enseignant['nom'] . ' ' . $enseignant['prenom']; ?> (<?php echo $enseignant['id']; ?>) - <?php echo $annee; ?></h2>
<table>
    <tr><th>UE</th><th>Type</th><th>Heures</th><th>Groupes</th><th>Part</th><th>HETD</th><th>Commentaire</th><th>Source</th></tr>
<?php foreach ($detail['lignes'] as $l) { ?>
    <tr>
        <td><?php echo $l['ue']; ?></td>
        <td><?php echo $l['type']; ?></td>
        <td class="nombre"><?php echo $l['heures']; ?></td>
        <td class="nombre"><?php echo $l['groupes']; ?></td>
        <td class="nombre"><?php echo $l['part']; ?></td>
        <td class="nombre"><?php echo number_format($l['etd'], 2, ',', ' '); ?></td>
        <td><?php echo $l['commentaire']; ?></td>
        <td><?php echo $l['source']; ?></td>
    </tr>
<?php } ?>
    <tr><th colspan="5">Total</th><th class="nombre"><?php echo number_format($detail['total'], 2, ',', ' '); ?></th><th colspan="2"></th></tr>
</table>

<h3>Saisie rapide d'une ligne</h3>
<form method="post" action="index.php?page=ajouter">
    <input type="hidden" name="teacher_id" value="<?php echo $enseignant['id']; ?>">
    <input type="hidden" name="annee" value="<?php echo $annee; ?>">
    UE <input name="ue" size="14">
    Type <input name="type" size="6">
    Heures <input name="heures" size="5">
    Groupes <input name="groupes" size="3" value="1">
    Part <input name="part" size="4" value="1">
    Commentaire <input name="commentaire" size="30">
    <button type="submit">Ajouter</button>
</form>
