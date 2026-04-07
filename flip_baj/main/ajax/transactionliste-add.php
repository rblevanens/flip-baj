<?php
namespace flip_baj\main\ajax;


use PDOException;

include ('../constantes.php');

if (isset($_POST["idliste"])) {
    if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }
    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }
    $idliste = $_POST["idliste"];
    $idtransaction = $_POST["id_transaction"];
    try {
        $statement = $pdo->prepare($SQL_24_transactionlisteadd);
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
        'id_transaction' => $idtransaction,
        'id_bourse_liste' => $idliste
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
