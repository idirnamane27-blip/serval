<h2>Années universitaires</h2>
<table>
    <tr><th>Année</th><th>État</th><th>Consultation</th></tr>
<?php foreach ($annees as $a) { ?>
    <tr>
        <td><?php echo $a['code']; ?></td>
        <td><?php echo $a['cloturee'] == 1 ? 'clôturée le ' . $a['date_cloture'] : 'ouverte'; ?></td>
        <td>
            <a href="index.php?page=services&amp;annee=<?php echo $a['code']; ?>">services</a> -
            <a href="index.php?page=ue&amp;annee=<?php echo $a['code']; ?>">par UE</a>
<?php if ($a['cloturee'] != 1) { ?>
            - <a href="index.php?page=cloturer&amp;annee=<?php echo $a['code']; ?>">clôturer l'année</a>
<?php } ?>
        </td>
    </tr>
<?php } ?>
</table>
