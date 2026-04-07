<?php
namespace flip_baj\main\ajax;


use PDOException;

include ('../constantes.php');
include ('../pdo_connect.php');

if (is_null($pdo)) {
    die('Could not connect to database!');
}

try {
    $statement = $pdo->prepare($SQL_1_selectionvendeur);
} 
catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}

$statement->execute();
$totalData = 0;
$data = array();

while ($vendeur = $statement->fetch()) {
    $nestedData = array();
    $nestedData[] = $vendeur['nom'];
    $nestedData[] = $vendeur['prenom'];
    $nestedData[] = $vendeur['email'];
    $nestedData[] = $vendeur['telephone'];
    $nestedData[] = $vendeur['nbjeuxrendus'];
    $nestedData[] = $vendeur['nbjeuxvendus'];
    $nestedData[] = $vendeur['nbjeuxstock'];
    $nestedData[] = $vendeur['nbjeuxpasrecus'];
    $nestedData[] = '<a alt="Editer le vendeur ' . $vendeur['idDuVendeur'] . '" class="editVendeur" href="#" onclick="edit_user(\'' . $totalData . '\');" data-vendeur="' . $vendeur['idDuVendeur'] . '"></a>';
    $totalData ++;
    $data[] = $nestedData;
}

// error_log("totalData\n"+$totalData, 0);

$json_data = array(
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalData),
    "data" => $data // total data array
);

// error_log("json_data\n".$json_data.'\n', 0);

echo json_encode($json_data);
$statement = null;
$pdo = null;
?>
