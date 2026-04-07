<?php
namespace flip_baj\main\ajax;

use PDOException;

/**Ce fichier contient l'accès en base pour récupérer la somme en caisse.
 * */
include ('../constantes.php');

if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die();
}
include ('../pdo_connect.php');
if (is_null($pdo)) {
    die('Could not connect to database!');
}
try {
    $statement = $pdo->prepare($SQL_8_argentencaisse_get);
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}
$statement->execute();
if ($total = $statement->fetch()) {
    if ($total['Total'] != null)
        echo json_encode(array(
            "message1" => $total['Total'],
            "message2" => '1'
        ));
    else {
        echo json_encode(array(
            "message1" => '0',
            "message2" => '1'
        ));
    }
    $statement = null;
    $pdo = null;
}

?>