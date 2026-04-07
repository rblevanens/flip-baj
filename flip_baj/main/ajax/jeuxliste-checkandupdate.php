<?php
namespace flip_baj\main\ajax;

use PDOException;
use function flip_baj\main\VerifCodeBarre;

include ('../constantes.php');
include ('../utils.php');
include ('../pdo_connect.php');

if (isset($_POST["id"])) {
    if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }
    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }
    $id = $_POST["id"];
    $statut = $_POST["statut"];
    $ip = $_POST["ip"];
    $codebarre = '';
    if (isset($_POST["id"])) {
        // error_log("from id".$_POST["value"], 0);
        $codebarre = VerifCodeBarre($_POST["value"]);
    }
    // tente de récupérer le code barre dans un attrribut nommé value (si on vient d'une colonnne editable)
    if (isset($_POST["value"])) {
        // error_log("value".$_POST["value"], 0);
        // si le formulaire soumet un codebarre vide on ne fait rien
        if ($_POST["value"] == '') {
            echo ('dblclick pour code barre');
            die();
        }
        // test si le code barre est bon, sinon on ne fait rien
        $re = "/^\d{4}$/";
        if (! preg_match($re, $_POST["value"])) {
            echo ('code barre invalide');
            die();
        } 
        else {
            $codebarre = VerifCodeBarre($_POST["value"]);
        }
    }
    // si le code barre est incorrect on ne fait RecursiveCachingIterator
    if ($codebarre == '') {
        echo ('dblclick pour code barre');
        die();
    }

    // error_log("codebarre=".$codebarre, 0);
    try {
        $statement = $pdo->prepare($SQL_4_checkcodebarre);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $statement->execute([
        'code_barre' => $codebarre
    ]);

    if ($statement->fetch()) {
        echo json_encode("Code déjà pris", JSON_UNESCAPED_UNICODE);
        //echo ('<span class="codedejapris">Code déjà pris</span>');
        $statement = null;
        $pdo = null;
        die();
    } else {
        // error_log("update:".$codebarre, 0);
        $a_json_row = array();
        $a_json_row["id"] = $id;
        $a_json_row["codebarre"] = $codebarre;
        $a_json_row["statut"] = $statut;
        include ('../pdo_connect.php');
        if (is_null($pdo)) {
            die('Could not connect to database!');
        }
        $rsql=$SQL_13_updatestatutlistejeu.$SQL_13_01_code.$SQL_13_02_iddepot.$SQL_13_05_whereid ;
        try {
            $statement = $pdo->prepare($rsql); // Change le statut, le code_barre et l'id_depot
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
            'statut' => $statut,
            'code_barre' => $codebarre,
            'id_depot' => $ip,
            'date_reception' => date("Y-m-d H:i:s"),
            'id' => $id
        ])) {
            echo json_encode(array(
                "message1" => $statement->error,
                "message2" => '0'
            ));
        } else {
            echo json_encode(array(
                "message1" => $statement->rowCount(),
                "message2" => $a_json_row
            ));
            $paramJournal = array(
                'id_liste' => $id,
                'old_id_statut' => isset($_POST["old_id_statut"]) ? $_POST["old_id_statut"] : 0,
                'new_id_statut' => $statut,
                'ip' => $_SERVER['REMOTE_ADDR'], // Utilisez l'adresse IP du client
                'date' => date('Y-m-d H:i:s') // Date actuelle
            );
            
            try {
                $statementJournal = $pdo->prepare($SQL_30_journalStatut);
                $statementJournal->execute($paramJournal);
            } catch (PDOException $e) {
                // Gestion des erreurs pour l'insertion dans la table journal
                echo json_encode(array(
                    "message3" => $e->getMessage(),
                    "message4" => '0'
                ));
                die();
            }
        }

        $statement = null;
        $pdo = null;
    }
}

?>
