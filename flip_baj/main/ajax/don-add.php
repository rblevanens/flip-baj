<?php
namespace flip_baj\main\ajax;

use PDOException;

include ('../constantes.php');
if (isset($_POST["id"])) {
    if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }
    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }
    try {
        $statement = $pdo->prepare($SQL_19_donadd);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $id = $_POST["id"];
    $montant_don = $_POST["montant_don"];
    $type_don = $_POST["type_don"];

    if (! $statement) {
        echo json_encode(array(
            "message1" => $pdo->error,
            "message2" => '0'
        ));
    }
    if (! $statement->execute([
        'idDuVendeur' => $id,
        'montant' => $montant_don,
        'date' => date("Y-m-d H:i:s"),
        'type' => $type_don
    ])) {
        echo json_encode(array(
            "message1" => $statement->error,
            "message2" => '0'
        ));
    } else {
        // error_log("insert!", 0);
        echo json_encode(array(
            "message1" => $pdo->lastInsertId(),
            "message2" => '1'
        ));
    }

    $statement = null;
    $pdo = null;
}
?>
