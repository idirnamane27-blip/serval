<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>SERVAL - services d'enseignement</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>SERVAL</h1>
    <p><?php echo $config['etablissement']; ?></p>
    <nav>
        <a href="index.php">Années</a>
        <a href="index.php?page=services&amp;annee=<?php echo urlencode($annee); ?>">Services</a>
        <a href="index.php?page=ue&amp;annee=<?php echo urlencode($annee); ?>">Par UE</a>
        <a href="index.php?page=imports">Imports</a>
    </nav>
</header>
<main>
<?php echo $contenu; ?>
</main>
<footer>
    Données arrêtées au <?php echo strftime('%d/%m/%Y à %H:%M'); ?> - SERVAL v2.4
</footer>
</body>
</html>
