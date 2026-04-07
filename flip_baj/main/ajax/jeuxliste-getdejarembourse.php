<?php
namespace flip_baj\main\ajax;


use PDOException;

/**Ce fichier contient l'accès en base pour récupérer les sommes déjà versées au vendeur et celles données à l'alchimie.
 * Il prend en argument le vendeur concerné et renvoie le total des 2 sommes.
 * */
include ('../constantes.php');
if (isset($_POST["id"])) {
    if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }
    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }
    $argent = 0;
    $id = $_POST["id"];
    try {
        $statement = $pdo->prepare($SQL_17A_getargentdejapaye);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $statement->execute([
        'id' => $id,
    ]);
    if ($total = $statement->fetch()) {
        if ($total['remb'] != null){
            $argent += $total['remb'];
        }
    }
    try {
        $statement = $pdo->prepare($SQL_17B_getargentdejapaye);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $statement->execute([
        'id' => $id,
    ]);
    if ($total = $statement->fetch()) {
        if ($total['don'] != null){
            $argent += $total['don'];
        }
    }
    if ($argent != null){
            echo json_encode(array(
                "message1" => $argent,
                "message2" => '1'
            ));}
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
