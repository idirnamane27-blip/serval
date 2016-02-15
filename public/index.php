<?php
// SERVAL - consultation des services (v1.0)
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/AcademicYear.php';
require __DIR__ . '/../src/Repository/TeacherRepository.php';
require __DIR__ . '/../src/Repository/ServiceRepository.php';
require __DIR__ . '/../src/Calculator/ServiceCalculator.php';

$pdo = Serval\Database::get();
$annee = isset($_GET['annee']) ? $_GET['annee'] : Serval\AcademicYear::current();
$enseignants = new Serval\Repository\TeacherRepository($pdo);
$services = new Serval\Repository\ServiceRepository($pdo);
?>
<html>
<head><meta charset="utf-8"><title>SERVAL</title><link rel="stylesheet" href="style.css"></head>
<body>
<h1>Services <?php echo $annee; ?></h1>
<table>
<tr><th>Enseignant</th><th>Statut</th><th>Total (h TD)</th></tr>
<?php foreach ($enseignants->allByName() as $t) {
    $calc = new Serval\Calculator\ServiceCalculator();
    $total = $calc->total($services->findByTeacherAndYear($t['id'], $annee)); ?>
<tr><td><?php echo $t['nom'] . ' ' . $t['prenom']; ?></td><td><?php echo $t['statut']; ?></td><td><?php echo number_format($total, 2, ',', ' '); ?></td></tr>
<?php } ?>
</table>
</body>
</html>
