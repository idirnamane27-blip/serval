<h2>Heures par UE - <?php echo $annee; ?></h2>
<table>
    <tr><th>UE</th><th>Lignes</th><th>Heures</th><th>HETD</th></tr>
<?php foreach ($stats as $s) { ?>
    <tr>
        <td><?php echo $s['ue']; ?></td>
        <td class="nombre"><?php echo $s['nb_lignes']; ?></td>
        <td class="nombre"><?php echo number_format((float) $s['heures'], 2, ',', ' '); ?></td>
        <td class="nombre"><?php echo number_format((float) $s['hetd'], 2, ',', ' '); ?></td>
    </tr>
<?php } ?>
</table>
