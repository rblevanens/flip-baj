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
    try {
        $statement = $pdo->prepare($SQL_18_getlistejeuxtotalvente);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $statement->execute([
        'id' => $id,
        'annee' => annee_base
    ]);
    if ($total = $statement->fetch()) {
        if ($total['rendu'] != null)
            echo json_encode(array(
                "message1" => $total['rendu'],
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
}

?>
