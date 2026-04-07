<?php
namespace flip_baj\main\ajax;


use PDOException; 
use function flip_baj\main\PrixRendu;

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die();
}
include ('../constantes.php');
include ('../utils.php');
include ('../pdo_connect.php');

if (is_null($pdo)) {
    die('Could not connect to the database!');
}

$sqlConditions = array();
$sqlParameters = array();

// Construire la requête en fonction des éléments POST
if (isset($_POST['idVendeur'])) {
    $sqlConditions[] = $SQL_5_1_whereVendeur;
    $sqlParameters['idVendeur'] = $_POST['idVendeur'];
}

if (isset($_POST['idStatut'])) {
    $sqlConditions[] = $SQL_5_2_whereStatut;
    $sqlParameters['idStatut'] = $_POST['idStatut'];
}

if (isset($_POST['vigilance'])) {
    $sqlConditions[] = $SQL_5_3_whereVigilance;
    $sqlParameters['vigilance'] = $_POST['vigilance'];
}

if (isset($_POST['code_barre'])) {
    $sqlConditions[] = $SQL_5_4_whereCode;
    $sqlParameters['code_barre'] = $_POST['code_barre'];
}

if (isset($_POST['nom_jeu'])) {
    $sqlConditions[] = $SQL_5_5_whereNom;
    $sqlParameters['nom_jeu'] = $_POST['nom_jeu'];
}

$sqlConditions[] = $SQL_5_6_whereAnnee;

// Construire la requête complète
$sqlQuery = $SQL_5_getlistejeux . implode(' AND ', $sqlConditions) . $SQL_5_7_orderBy;

try {
    $statement = $pdo->prepare($sqlQuery);
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}

$statement->execute($sqlParameters);

$data = array();
$totalData = 0;
while ($jeu = $statement->fetch()) {
    $nestedData = array(
        "Code" => $jeu['code_barre'],
        "Vendu" => $jeu['vendu'],
        "Vendeur" => $jeu['nom'] . " " . $jeu['prenom'],
        "DT_RowId" => $jeu['id'],
        "idstatut" => $jeu['id_statut'],
        "statut" => $jeu['statut'],
        "vigilance" => $jeu['vigilance'],
        "date_reception" => $jeu['date_reception'],
        "idvendeur" => $jeu['id_utilisateur'],
        "Jeu" => $jeu['nj'],
        "date_sortie_stock" => $jeu['date_sortie_stock']
    );
    $nestedData['Rendu'] = PrixRendu($nestedData['Vendu']);
    $totalData++;
    $data[] = $nestedData;
}

$json_data = array(
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalData),
    "data" => $data // total data array
);

echo json_encode($json_data);

$statement = null;
$pdo = null;
?>
