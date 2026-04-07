<?php
namespace flip_baj\main\ajax;

use PDOException;

include ('../constantes.php');
include ('../pdo_connect.php');
if (is_null($pdo)) {
    die('Could not connect to database!');
}
$term = trim(strip_tags($_GET['term']));
$exact = trim(strip_tags($_GET['exact']));

try {
    $statement = $pdo->prepare($SQL_3_getjeux);
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}
// error_log("term:".$term.'\n', 0);
if ($exact == '1')
    $newParameter = $term;
else
    $newParameter = '%' . $term . '%';
$statement->execute([
    'nom' => $newParameter
]);
$totalData = 0;
$a_json = array();
$a_json_row = array();
while ($jeu = $statement->fetch()) {
    $a_json_row["id"] = $jeu['id'];
    $a_json_row["label"] = $jeu['nom'];
    $a_json_row["value"] = $jeu['nom'];
    $totalData ++;
    array_push($a_json, $a_json_row);
}
// error_log("totalData\n"+$totalData, 0);
/*
 * $json_data = array(
 * "suggestions" => $data // total data array
 * );
 */
// error_log("json_data\n".$json_data.'\n', 0);
echo json_encode($a_json);
// error_log("res:".json_encode($a_json), 0);
$statement = null;
$pdo = null;
?>
