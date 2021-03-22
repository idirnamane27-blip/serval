<?php
declare(strict_types=1);

namespace Serval\Tests;

use PHPUnit\Framework\TestCase;
use Serval\Calculator\ServiceCalculator;

final class ServiceCalculatorTest extends TestCase
{
    private function ligne(string $type, float $heures, int $groupes = 1, float $part = 1.0): array
    {
        return ['type' => $type, 'heures' => $heures, 'groupes' => $groupes, 'part' => $part, 'etd_impose' => null];
    }

    /**
     * @dataProvider coefficients
     */
    public function testCoefficients(string $type, float $heures, float $attendu): void
    {
        $calc = new ServiceCalculator();
        $this->assertEquals($attendu, $calc->etdLigne($this->ligne($type, $heures)));
    }

    public function coefficients(): array
    {
        return [
            'CM'     => ['CM', 10.0, 15.0],
            'TD'     => ['TD', 10.0, 10.0],
            'TP'     => ['TP', 10.0, 6.7],
            'PROJET' => ['PROJET', 10.0, 5.0],
        ];
    }

    public function testDixHeuresDeTpFontSixVirguleSept(): void
    {
        $calc = new ServiceCalculator();
        $this->assertEquals(6.7, $calc->etdLigne($this->ligne('TP', 10.0)));
    }

    public function testGroupesEtPart(): void
    {
        $calc = new ServiceCalculator();
        $this->assertEquals(30.0, $calc->etdLigne($this->ligne('TD', 20.0, 3, 0.5)));
    }

    public function testTypeInconnuNonCompte(): void
    {
        $calc = new ServiceCalculator();
        $this->assertEquals(0.0, $calc->etdLigne($this->ligne('SEMINAIRE', 10.0)));
    }

    public function testHeuresNegativesIgnorees(): void
    {
        $calc = new ServiceCalculator();
        $this->assertEquals(0.0, $calc->etdLigne($this->ligne('TD', -4.0)));
    }

    public function testLeCalculRetourneUnResultat(): void
    {
        $calc = new ServiceCalculator();
        $result = $calc->total([$this->ligne('CM', 12.0), $this->ligne('TP', 16.0, 2)]);
        $this->assertNotNull($result);
    }
}
