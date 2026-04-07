<?php
namespace flip_baj\main\ajax;


use PDOException;
use function flip_baj\main\PrixRendu;

include ('../constantes.php');
include ('../utils.php');

if (isset($_POST["idVendeurEdition"])) {
    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }
    $idVendeurEdition = trim(strip_tags($_POST['idVendeurEdition']));
    $statutJeu = trim(strip_tags($_POST['statutJeu']));
    // error_log("ici:".$idVendeurEdition.":".$statutJeu, 0);
    $rsql = $SQL_5_getlistejeux.$SQL_5_1_whereVendeur.' AND'.$SQL_5_2_whereStatut;
    // si on veut une recherche par code ?
    if (! empty($_POST['columns'][0]['search']['value'])) {
        $rsql .= ' AND'.$SQL_5_4_whereCode;
    }
    $rsql .= ' AND'.$SQL_5_6_whereAnnee;
    // order by
    if (!empty($_POST['order'])) {
        $orderByColumnIndex = $_POST['order'][0]['column'];
        $orderByColumn = $_POST['columns'][$orderByColumnIndex]['name'];
        $orderByDirection = $_POST['order'][0]['dir'];
        
        $rsql .= ' ORDER BY ' . $orderByColumn . ' ' . $orderByDirection;
    }
    else{
        $rsql .= $SQL_5_7_orderBy;
    }
    try {
        $statement = $pdo->prepare($rsql);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    if (! empty($_POST['columns'][0]['search']['value'])) {
        if (! $statement->execute([
            'idVendeur' => $idVendeurEdition,
            'idStatut' => $statutJeu,
            'code_barre' => $_POST['columns'][0]['search']['value']
        ])) {
            error_log("Erreur SQL:" . $statement->error, 0);
        }
    } else {
        if (! $statement->execute([
            'idVendeur' => $idVendeurEdition,
            'idStatut' => $statutJeu
        ])) {
            error_log("Erreur SQL:" . $statement->error, 0);
        }
    }
    if (! $statement) {
        error_log("Erreur SQL:" . $pdo->error, 0);
    }
    
    $totalData = 0;
    $data = array();
    while ($jeu = $statement->fetch()) {
        $nestedData = array(
            "Code" => $jeu['code_barre'],
            "Jeu" => $jeu['nj'],
            "Vendu" => $jeu['vendu'],
            "vigilance" => $jeu['vigilance'],
            "DT_RowId" => $jeu['id'],
            "date_reception" => $jeu['date_reception'],
            "minprix" => $jeu['minprix'],
            "maxprix" => $jeu['maxprix'],
            "DateSortieStock" => $jeu['date_sortie_stock']
        );
        $nestedData["Rendu"] = PrixRendu($nestedData["Vendu"]);
        $nestedData["Commission"] = ceil($nestedData["Vendu"] / 6.0);

        $totalData ++;
        $data[] = $nestedData;
    }
    // error_log("totalData\n"+$totalData, 0);
    $json_data = array(
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalData),
        "data" => $data // total data array
    );
    // error_log("json_data".json_encode($json_data).'\n', 0);
    echo json_encode($json_data);
    $statement = null;
    $pdo = null;
}
?>