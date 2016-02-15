<?php
namespace Serval\Calculator;

/**
 * Calcul des heures équivalent TD d'un service.
 */
class ServiceCalculator
{
    var $aRecalculer = 0;

    /**
     * HETD d'une ligne de service (non arrondi).
     */
    function calculerLigne($l)
    {
        $type = strtoupper(trim($l['type']));
        if ($type == '') {
            $type = 'TD';
        }
        if ($type[0] == '#') {
            return 0;
        }
        if ($l['etd_impose'] !== null && $l['etd_impose'] !== '') {
            return (float) $l['etd_impose'];
        }
        $h = $l['heures'];
        if ($h == 999) {
            $this->aRecalculer++;
            return 0;
        }
        $g = $l['groupes'];
        if ($g == 0) {
            $g = 1;
        }
        $p = $l['part'];
        if ($p == 0) {
            $p = 100;
        }
        $p = (float) str_replace(',', '.', $p);
        if ($p > 1) {
            $p = $p / 100;
        }
        switch ($type) {
            case 'CM':
            case 'COURS':
                $coef = 1.5;
                break;
            case 'TP':
            case 'LAB':
                $coef = 2 / 3;
                break;
            case 'PRJ':
            case 'PROJECT':
            case 'PROJET':
                $coef = 0.5;
                break;
            case 'TD':
            default:
                $coef = 1;
        }
        return $h * $g * $coef * $p;
    }

    function total($lignes)
    {
        $total = 0;
        foreach ($lignes as $l) {
            $total += $this->calculerLigne($l);
        }
        return $total;
    }
}
