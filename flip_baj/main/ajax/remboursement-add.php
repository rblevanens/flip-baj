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
    $id = $_POST["id"];
    $montant_remb = $_POST["montant"];
    $type_remb = $_POST["type"];
    try {
        $statement = $pdo->prepare($SQL_36_remboursement_add);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    if (! $statement) {
        echo json_encode(array(
            "message1" => $pdo->error,
            "message2" => '0'
        ));
    }
    if (! $statement->execute([
        'id_utilisateur' => $id,
        'montant_remb' => $montant_remb,
        'date_remb' => date("Y-m-d H:i:s"),
        'type_remb' => $type_remb
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
