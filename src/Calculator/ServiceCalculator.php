<?php
declare(strict_types=1);

namespace Serval\Calculator;

/**
 * Calcul des heures équivalent TD (HETD).
 *
 * Moteur de calcul officiel depuis la refonte de 2021 : remplace LegacyServiceCalculator.
 */
final class ServiceCalculator
{
    public const COEFFICIENTS = [
        'CM'     => 1.5,
        'TD'     => 1.0,
        'TP'     => 0.67,
        'PROJET' => 0.5,
    ];

    private array $coefficients;

    public function __construct(?array $coefficients = null)
    {
        $this->coefficients = $coefficients ?? self::COEFFICIENTS;
    }

    /**
     * HETD d'une ligne de service, arrondi au centième.
     */
    public function etdLigne(array $ligne): float
    {
        if (isset($ligne['etd_impose']) && $ligne['etd_impose'] !== '') {
            return round((float) $ligne['etd_impose'], 2);
        }
        $type = strtoupper(trim((string) ($ligne['type'] ?? '')));
        if (!isset($this->coefficients[$type])) {
            return 0.0;
        }
        $heures = (float) ($ligne['heures'] ?? 0);
        if ($heures < 0) {
            return 0.0;
        }
        $groupes = max(1, (int) ($ligne['groupes'] ?? 1));
        $part = (float) ($ligne['part'] ?? 0);
        if ($part > 1) {
            $part = $part / 100;
        }
        return round($heures * $groupes * $this->coefficients[$type] * $part, 2);
    }

    public function total(array $lignes): float
    {
        $total = 0.0;
        foreach ($lignes as $ligne) {
            $total += $this->etdLigne($ligne);
        }
        return round($total, 2);
    }
}
