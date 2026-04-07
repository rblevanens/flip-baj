<?php
namespace flip_baj\main\ajax;


use PDOException, PDO;

if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die();
}

include ('../pdo_connect.php');
include ('../constantes.php');

if (is_null($pdo)) {
    die('Could not connect to the database!');
}
$rsql=$SQL_31_getacheteur;
$param = array();

if (isset($_POST['email'])) {
    $param['email'] = "%" . $_POST['email'] . "%";
    $rsql .= $SQL_31_byemail;
}
if (isset($_POST['nom'])) {
    $param['nom'] = "%".$_POST['nom']."%";
    $rsql .= $SQL_31_bynom;
}
if (isset($_POST['prenom'])) {
    $param['prenom'] = "%".$_POST['prenom']."%";
    $rsql .= $SQL_31_byprenom;
}
try {
    $statement = $pdo->prepare($rsql);
    $statement->execute($param);
    $acheteurs = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($acheteurs);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Erreur de base de données'
    ]);
}

?>
