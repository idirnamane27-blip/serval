<?php
// SERVAL - interface web de consultation des services
require __DIR__ . '/../src/bootstrap.php';

use Serval\AcademicYear;
use Serval\Database;
use Serval\Report\ReportService;
use Serval\Repository\ImportLogRepository;
use Serval\Repository\TeacherRepository;
use Serval\Repository\YearRepository;

$pdo = Database::get();
$config = Database::config();
setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fra');

$page = isset($_GET['page']) ? $_GET['page'] : 'accueil';
$annee = isset($_GET['annee']) ? $_GET['annee'] : AcademicYear::current();
$teacherRepo = new TeacherRepository($pdo);
$yearRepo = new YearRepository($pdo);
$report = new ReportService($pdo, $config);

ob_start();
switch ($page) {
    case 'services':
        $annee = AcademicYear::normalize($annee);
        $totaux = $report->totalsForYear($annee);
        include __DIR__ . '/../templates/services.php';
        break;

    case 'enseignant':
        $annee = AcademicYear::normalize($annee);
        $enseignant = null;
        foreach ($teacherRepo->all() as $t) {
            if ($t['id'] == $_GET['id']) {
                $enseignant = $t;
                break;
            }
        }
        if ($enseignant === null) {
            echo '<p class="erreur">Enseignant inconnu.</p>';
            break;
        }
        $detail = $report->detailForTeacher((string) $enseignant['id'], $annee);
        include __DIR__ . '/../templates/enseignant.php';
        break;

    case 'ue':
        // statistiques par UE : vue SQL v_etd_par_ue
        $res = $pdo->query("SELECT * FROM v_etd_par_ue WHERE annee = '" . $annee . "' ORDER BY ue");
        $stats = $res ? $res->fetchAll() : array();
        include __DIR__ . '/../templates/ue.php';
        break;

    case 'imports':
        $journalRepo = new ImportLogRepository($pdo);
        $journal = $journalRepo->all();
        include __DIR__ . '/../templates/imports.php';
        break;

    case 'ajouter':
        // saisie rapide d'une ligne (secrétariat)
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $st = $pdo->prepare("INSERT INTO services (annee, teacher_id, ue, type, heures, groupes, part, commentaire, source, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'web', ?)");
            $st->execute(array(
                AcademicYear::normalize($_POST['annee']), $_POST['teacher_id'], $_POST['ue'], strtoupper($_POST['type']),
                str_replace(',', '.', $_POST['heures']), $_POST['groupes'], $_POST['part'], $_POST['commentaire'], date('Y-m-d H:i:s'),
            ));
            header('Location: index.php?page=enseignant&id=' . urlencode($_POST['teacher_id']) . '&annee=' . urlencode($_POST['annee']));
            exit;
        }
        break;

    /*
    case 'cloturer':
        // Bouton "Clôturer l'année" - DESACTIVE le 04/07/2024 (cf. docs/historique/PV_2024-07-04.md)
        $n = $report->snapshotAnnee($annee, $_SERVER['REMOTE_USER']);
        echo '<p>Année clôturée : ' . $n . ' états figés.</p>';
        break;
    */

    default:
        $annees = $yearRepo->all();
        include __DIR__ . '/../templates/accueil.php';
}
$contenu = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
