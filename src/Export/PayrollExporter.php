<?php
namespace Serval\Export;

/**
 * Export des heures complémentaires vers la paie.
 */
class PayrollExporter
{
    var $db;
    var $separateur = ';';

    function __construct($db)
    {
        $this->db = $db;
    }

    function exporter($annee, $fichier)
    {
        $res = $this->db->query("SELECT t.id, t.nom, t.prenom, h.heures, h.taux FROM heures_comp h JOIN teachers t ON t.id = h.teacher_id WHERE h.annee = '" . $annee . "'");
        if (!$res) {
            return 0;
        }
        $fp = fopen($fichier, 'w');
        $n = 0;
        while ($ligne = $res->fetch()) {
            $nom = utf8_decode($ligne['nom']); // la paie attend de l'ISO-8859-1
            if ($nom{0} == '#') {
                continue;
            }
            $montant = (real) $ligne['heures'] * (real) $ligne['taux'];
            fputs($fp, implode($this->separateur, array(
                (int) $ligne['id'],
                $nom,
                utf8_decode($ligne['prenom']),
                number_format($montant, 2, ',', ''),
            )) . "\r\n");
            $n++;
        }
        fclose($fp);
        return $n;
    }
}
