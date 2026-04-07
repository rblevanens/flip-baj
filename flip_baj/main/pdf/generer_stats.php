<?php
namespace flip_baj\main\pdf;

use PDOException;

include ('../pdo_connect.php');
include ('../constantes.php');
require_once ('../constantes.php');
require_once ('PDF.php');

if (is_null($pdo)) {
    die('Could not connect to database!');
}

if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
    http_response_code(400);
    echo "Dates manquantes.";
    exit;
}

$date_debut = $_POST['start_date'];
$date_fin = $_POST['end_date'];

GenererStats($date_debut, $date_fin);

/**
 * Fonction principale de génération du PDF.
 */
function GenererStats($date_debut_festival, $date_fin_festival)
{
    global $pdo, $SQL_48B_get_stats_jeux_byDay, $SQL_48_get_stats_jeux_byHour, $SQL_49_get_stats_trans_byHour,
           $SQL_50_get_stats_vendeurs, $SQL_51_get_stats_duree_stock, $SQL_52_get_stats_trans_fin,
           $SQL_53_get_stats_jeux_fin, $SQL_29_getInfosDons, $SQL_15_01_get_infos_exemplaire;

    $jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];

    // Ajoute ici toutes les requêtes préparées et leur exécution (comme tu l’as déjà fait dans ton code initial)

    // ... [reprends toute la logique que tu avais dans ta fonction GenererStats ici comme tu l'as bien structurée] ...
    
    // Exemple de fin (PDF output)
    $pdf = new \PDF();
    $pdf->AliasNbPages();
    $pdf->title = mb_convert_encoding("Statistiques de la Bourse Aux Jeux pour l'édition ", 'ISO-8859-15', 'UTF-8') . date("Y");
    $pdf->SetAuthor("Association Woopy On Off");
    $pdf->SetTitle($pdf->title);
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 10, "PDF généré avec succès.", 0, 1);
    $pdf->Output('F', '../pdf/pdf/stats_' . date('Y') . '.pdf');

    echo "PDF généré avec succès.";
}
?>
