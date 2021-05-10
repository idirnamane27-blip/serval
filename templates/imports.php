<h2>Journal des imports</h2>
<table>
    <tr><th>#</th><th>Fichier</th><th>Année</th><th>Statut</th><th>Lignes</th><th>Message</th><th>Début</th><th>Fin</th></tr>
<?php foreach ($journal as $j) { ?>
    <tr<?php echo $j['statut'] != 'OK' ? ' class="alerte"' : ''; ?>>
        <td><?php echo $j['id']; ?></td>
        <td><?php echo $j['fichier']; ?></td>
        <td><?php echo $j['annee']; ?></td>
        <td><?php echo $j['statut']; ?></td>
        <td class="nombre"><?php echo $j['nb_lignes']; ?></td>
        <td><?php echo $j['message']; ?></td>
        <td><?php echo $j['started_at']; ?></td>
        <td><?php echo $j['finished_at']; ?></td>
    </tr>
<?php } ?>
</table>
