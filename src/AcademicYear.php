<?php
namespace Serval;

/**
 * Années universitaires.
 *
 * Forme canonique : AAAA-AAAA (ex. 2024-2025).
 */
class AcademicYear
{
    /**
     * Ramène une année universitaire à la forme canonique AAAA-AAAA.
     * Écritures acceptées : 2024-2025, 2024/2025, 24-25, 24/25, 20242025, 2024 (année de rentrée).
     */
    public static function normalize($annee)
    {
        $a = trim((string) $annee);
        if (preg_match('#^(\d{4})\s*[-/]\s*(\d{4})$#', $a, $m)) {
            $debut = (int) $m[1];
            $fin = (int) $m[2];
        } elseif (preg_match('#^(\d{2})\s*[-/]\s*(\d{2})$#', $a, $m)) {
            $debut = self::siecle($m[1]);
            $fin = self::siecle($m[2]);
        } elseif (preg_match('#^(\d{4})(\d{4})$#', $a, $m)) {
            $debut = (int) $m[1];
            $fin = (int) $m[2];
        } elseif (preg_match('#^\d{4}$#', $a)) {
            $debut = (int) $a;
            $fin = $debut + 1;
        } else {
            throw new \InvalidArgumentException("Annee universitaire invalide : '" . $annee . "'");
        }
        if ($fin != $debut + 1) {
            throw new \InvalidArgumentException("Annee universitaire incoherente : '" . $annee . "'");
        }
        return $debut . '-' . $fin;
    }

    private static function siecle($aa)
    {
        $n = (int) $aa;
        return $n >= 70 ? 1900 + $n : 2000 + $n;
    }

    /** Toutes les écritures d'une même année rencontrées en base. */
    public static function variants($annee)
    {
        list($d, $f) = explode('-', self::normalize($annee));
        return array(
            $d . '-' . $f,
            $d . '/' . $f,
            substr($d, 2) . '-' . substr($f, 2),
            substr($d, 2) . '/' . substr($f, 2),
            $d . $f,
            $d,
        );
    }

    /** Année universitaire en cours (bascule au 1er septembre). */
    public static function current()
    {
        $annee = (int) date('Y');
        $mois = (int) date('n');
        if ($mois >= 9) {
            return $annee . '-' . ($annee + 1);
        }
        return ($annee - 1) . '-' . $annee;
    }
}
