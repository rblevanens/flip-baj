<?php
namespace flip_baj\main\ajax;

include ('../constantes.php');
if (isset($_POST["CodeBarreAjout"]) || isset($_POST["CodeBarreAjoutAdmin"]) ) {
    if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }
    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }

    if (isset($_POST["CodeBarreAjout"])) {
        $CodeBarreAjout = $_POST["CodeBarreAjout"];
    } else if (isset($_POST["CodeBarreAjoutAdmin"])) {
        $CodeBarreAjout = $_POST["CodeBarreAjoutAdmin"];
    }

    $statement = $pdo->prepare($SQL_4_checkcodebarre);
    $statement->execute(['code_barre' => $CodeBarreAjout]);
    $res=$statement->fetch();
    if (! isset($res['code_barre'])) {
        //error_log("deja", 0);
        echo json_encode(array(
            "message1" => '',
            "message2" => '1'
        ));
    } else {
        //error_log("vide", 0);
        echo json_encode(array(
            "message1" => '<p class="bg-danger">Ce code-barre est déjà utilisé pour un autre jeu.</p>',
            "message2" => '0'
        ));
    }
    $statement = null;
    $pdo = null;
}
?>
