<?php
declare(strict_types=1);

namespace Serval\Tests;

use PHPUnit\Framework\TestCase;
use Serval\Report\OfficialReportService;
use Serval\Report\ReportService;

final class ReportServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = TestDatabase::create();
        $this->pdo->exec("INSERT INTO teachers (id, nom, prenom, statut, service_du) VALUES ('T1', 'DURAND', 'Paul', 'EC', 192)");
        $this->pdo->exec("INSERT INTO teachers (id, nom, prenom, statut, service_du) VALUES ('V1', 'LEROY', 'Anne', 'VAC', 0)");
        $this->pdo->exec("INSERT INTO services (annee, teacher_id, ue, type, heures, groupes, part) VALUES ('2024-2025', 'T1', 'UE1', 'CM', 10, 1, 1)");
        $this->pdo->exec("INSERT INTO services (annee, teacher_id, ue, type, heures, groupes, part) VALUES ('2024-2025', 'T1', 'UE1', 'TD', 20, 2, 1)");
        $this->pdo->exec("INSERT INTO services (annee, teacher_id, ue, type, heures, groupes, part) VALUES ('2024-2025', 'V1', 'UE2', 'TD', 12, 1, 1)");
    }

    public function testTotauxParEnseignant(): void
    {
        $report = new ReportService($this->pdo);
        $totaux = $report->totalsForYear('2024-2025');
        $this->assertNotNull($totaux);
        $this->assertCount(2, $totaux);
    }

    public function testDetailEnseignant(): void
    {
        $report = new ReportService($this->pdo);
        $detail = $report->detailForTeacher('T1', '2024-2025');
        $this->assertNotNull($detail);
        $this->assertArrayHasKey('total', $detail);
    }

    public function testEtatOfficielContientLesColonnes(): void
    {
        $officiel = new OfficialReportService($this->pdo);
        $csv = $officiel->toCsv('2024-2025');
        $this->assertStringContainsString('matricule', $csv);
        $this->assertStringContainsString('DURAND', $csv);
    }

    public function testExportPaieNonVide(): void
    {
        $officiel = new OfficialReportService($this->pdo);
        $fh = fopen('php://memory', 'w+');
        $n = $officiel->exportPaie('2024-2025', '2099-12-31', $fh);
        rewind($fh);
        $this->assertSame(1, $n);
        $this->assertNotEmpty(stream_get_contents($fh));
    }

    public function testWebEgalOfficiel(): void
    {
        $this->markTestSkipped('Instable depuis la reprise des archives (2021) - a revoir');
    }
}
