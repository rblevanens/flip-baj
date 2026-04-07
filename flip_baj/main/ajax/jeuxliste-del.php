<?php
namespace flip_baj\main\ajax;
use PDOException;

include ('../constantes.php');
include ('../pdo_connect.php');

if (isset($_POST["id"])) {
    if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }
    $id = $_POST["id"];
    try {
        $statement = $pdo->prepare($SQL_12_dellistejeu);
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
        'id' => $id
    ])) {
        echo json_encode(array(
            "message1" => $statement->error,
            "message2" => '0'
        ));
    } else {
        echo json_encode(array(
            "message1" => $statement->affected_rows,
            "message2" => '1'
        ));
        $paramJournal = array(
            'id_liste' => $id,
            'old_id_statut' => 1,
            'new_id_statut' => 9,
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
}
$pdo = null;
?>
