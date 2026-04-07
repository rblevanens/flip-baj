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
        $statement = $pdo->prepare($SQL_6_setvigilance);
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
        'id'=>$_POST["id"]
    ])) {
        echo json_encode(array(
            "message1" => $statement->error,
            "message2" => '0'
        ));
    } else {
        echo json_encode(array(
            "message1" => $statement->rowCount(),
            "message2" => '1'
        ));
    }
    
    $statement = null;
    $pdo = null;
}

?>