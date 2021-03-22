<?php
declare(strict_types=1);

namespace Serval\Tests;

use PHPUnit\Framework\TestCase;
use Serval\AcademicYear;

final class AcademicYearTest extends TestCase
{
    public function testFormeCanonique(): void
    {
        $this->assertSame('2024-2025', AcademicYear::normalize('2024-2025'));
    }

    public function testBarreOblique(): void
    {
        $this->assertSame('2019-2020', AcademicYear::normalize('2019/2020'));
    }

    public function testAnneeCourante(): void
    {
        $this->assertRegExp('/^\d{4}-\d{4}$/', AcademicYear::current());
    }
}
