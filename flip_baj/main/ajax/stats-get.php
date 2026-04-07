<?php
namespace flip_baj\main\ajax;


use PDOException;

/**Ce document fait l'ensemble des accès en base pour alimenter la page de stats. 
 * 
 * C'est un ajax, appelé par la fonction js @see updateStats().
 */
$annee = $_POST["annee"];

$a_json = GetStats($annee);

if (count($a_json) > 0) {
    echo json_encode($a_json);
} else {
    echo json_encode('');
}

/**
 * Cette fonction permet de récupérer l'ensembles des informations nécessaires à la page des stats.
 *
 * $jeux va stocker le résultat de la requête tirée de al_bourse_liste
 * $vente va stocker le résultat de la requête tirée de v_bourse_transactions
 * $remb va stocker le résultat de la requête tirée de al_bourse_remboursements
 * $dons va stocker le résultat de la requête tirée de al_bourse_dons
 * 
 * @param {Integer} $annee
 *            - sur 4 chiffres
 *            
 * @return {Array} $a_json - Encodé en Json, contient :
 *         {Integer} $a_json['nbJeuxEnregistres']
 *         {Integer} $a_json['nbJeuxStock']
 *         {Integer} $a_json['nbJeuxVendus']
 *         {Integer} $a_json['nbJeuxDonnés']
 *         {Integer} $a_json['VentesCB']
 *         {Integer} $a_json['VentesEspeces']
 *         {Integer} $a_json['VentesCheque']
 *         {Integer} $a_json['VentesSac'] - Nombre de sacs vendus
 *         {Integer} $a_json['totalStockPrixVendu'] - Valorisation du stock
 *         {Integer} $a_json['RembEspeces']
 *         {Integer} $a_json['RembCheques']
 *         {Integer} $a_json['RembPaypal']
 *         {Integer} $a_json['totalRemb'] - Total déjà remboursé
 *         {Integer} $a_json['totalARendre'] - Somme prix rendu des jeux vendus
 *         {Integer} $a_json['totalDons']
 */
function GetStats($annee)
{
    include ('../constantes.php');
    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }

    $a_json = array();

    try {
        $statement = $pdo->prepare($SQL_26_getInfosJeux);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $statement->execute([
        'annee' => $annee
    ]);
    $jeux = $statement->fetch();
    $a_json["nbJeuxEnregistres"] = $jeux['nbEnregistres'];
    $a_json["nbJeuxStock"] = $jeux['nbEnStock'];
    $a_json["nbJeuxVendus"] = $jeux['nbVendu'];
    $a_json["nbJeuxDonnés"] = $jeux['nbDonnes'];
    $a_json["totalStockPrixVendu"] = $jeux['ResteAVendre'];
    $a_json["totalARendre"] = $jeux['TotalARendre'];
   

    

    $a_json["totalDonsNonRemb"] = isset($jeux['totalDonsNonRemb']) ? $jeux['totalDonsNonRemb'] : 0;

    
    $a_json["commissionTTC"] = $jeux['CommissionTTC'];
    $a_json["commissionHT"] = $jeux['CommissionHT'];

    $statement->closecursor();

    try {
        $statement = $pdo->prepare($SQL_27_getInfosVentes);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $statement->execute([
        'annee' => $annee
    ]);
    $vente = $statement->fetch();
    $a_json["VentesCB"] = $vente['TotalCB'];
    $a_json["VentesEspeces"] = $vente['TotalEspeces'];
    $a_json["VentesCheque"] = $vente['TotalCheque'];
    $a_json["VentesSac"] = $vente['nbSacs'];
    
    $statement->closecursor();
    
    try {
        $statement = $pdo->prepare($SQL_28_getInfosRemb);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $statement->execute([
        'annee' => $annee
    ]);
    $remb = $statement->fetch();
    $a_json["RembEspeces"] = $remb['rembEspeces'];
    $a_json["RembCheques"] = $remb['rembCheque'];
    $a_json["RembPaypal"] = $remb['rembPaypal'];
    $a_json["totalRemb"] = $remb['totalRemb'];
    
    $statement->closecursor();

    try {
        $statement = $pdo->prepare($SQL_29_getInfosDons);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $statement->execute([
        'annee' => $annee
    ]);
    $dons = $statement->fetch();
    $a_json["totalDons"] = $dons['totalDons'];
    
    $statement = null;
    $pdo = null;
    return $a_json;
}

?>