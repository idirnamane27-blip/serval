<h2>Services <?php echo $annee; ?></h2>
<table>
    <tr><th>Matricule</th><th>Enseignant</th><th>Statut</th><th>Service dû</th><th>Total HETD</th><th>Taux</th><th></th></tr>
<?php foreach ($totaux as $l) {
    $t = $l['teacher'];
    $taux = @($l['total'] / $l['service_du']);
    ?>
    <tr<?php echo $l['alerte'] ? ' class="alerte"' : ''; ?>>
        <td><?php echo $t['id']; ?></td>
        <td><a href="index.php?page=enseignant&amp;id=<?php echo urlencode($t['id']); ?>&amp;annee=<?php echo $annee; ?>"><?php echo $t['nom'] . ' ' . $t['prenom']; ?></a></td>
        <td><?php echo $t['statut']; ?></td>
        <td class="nombre"><?php echo number_format($l['service_du'], 0, ',', ' '); ?></td>
        <td class="nombre"><?php echo number_format($l['total'], 2, ',', ' '); ?></td>
        <td class="nombre"><?php echo (is_infinite($taux) || is_nan($taux)) ? '-' : round($taux * 100) . ' %'; ?></td>
        <td><?php echo $l['alerte'] ? 'sous-service' : ''; ?></td>
    </tr>
<?php } ?>
</table>
<p class="note">Totaux calculés par le moteur HETD (TP = 0,67).</p>
