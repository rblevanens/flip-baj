<?php
namespace flip_baj\main\ajax;

use PDOException;
use function flip_baj\main\PrixRendu2Prix;

include ('../constantes.php');
include ('../utils.php');

if (isset($_POST["id"])) {
    $id = $_POST["id"];
    $value = trim(strip_tags($_POST['value']));
    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }

    $value = PrixRendu2Prix($value);
    try {
        $statement = $pdo->prepare($SQL_7_updateprixjeu);
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
        'vendu' => $value,
        'id' => $id
    ])) {
        echo json_encode(array(
            "message1" => $statement->error,
            "message2" => '0'
        ));
    }

    echo json_encode($_POST['value']);

    $statement = null;
    $pdo = null;
}
?>
