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
    $ip = $_POST["ip"];
    try {
        $statement = $pdo->prepare($SQL_15_01_get_infos_exemplaire);
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
    $statement->execute([
        'id' => $id
    ]);
    
    $res=$statement->fetch();
    $nomjeu = $res["nom_jeu"];
    $idVendeurEdition = '1';
    $codebarre = $res["code_barre"];
    $statut = $res["statut"];
    $vendu = $res["vendu"];
    
    $statement->closeCursor();
    
    try {
        $statement = $pdo->prepare($SQL_11_insertlistejeu);
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
    $statement->execute([
        'idVendeurEdition' => $idVendeurEdition,
        'nom_jeu' => $nomjeu,
        'vendu' => $vendu,
        'statut' => $statut,
        'codebarre' => $codebarre,
        'vigilance' => 0,
        'ip' => $ip,
        'date_reception'=> date('Y-m-d H:i:s'),
        'annee' => annee_base
    ]); 
    $id_insert = $pdo->lastInsertId();
    $paramJournal1 = array(
        'id_liste' => $id_insert,
        'old_id_statut' => 11,
        'new_id_statut' => 2,
        'ip' => $_SERVER['REMOTE_ADDR'], // Utilisez l'adresse IP du client
        'date' => date('Y-m-d H:i:s') // Date actuelle
    );
    
    try {
        $statementJournal = $pdo->prepare($SQL_30_journalStatut);
        $statementJournal->execute($paramJournal1);
    } catch (PDOException $e) {
        // Gestion des erreurs pour l'insertion dans la table journal
        echo json_encode(array(
            "message3" => $e->getMessage(),
            "message4" => '0'
        ));
        die();
    }
   
    $statement->closeCursor();
    
    try {
        $statement = $pdo->prepare($SQL_15_02_update_liste);
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
        'id' => $id,
        'don_le'=> date('Y-m-d H:i:s')
    ])) {
        echo json_encode(array(
            "message1" => $statement->error,
            "message2" => '0'
        ));
    } else {
        echo json_encode(array(
            "message1" => 'It works',
            "message2" => '1'
        ));
        $paramJournal = array(
            'id_liste' => $id,
            'old_id_statut' => 2,
            'new_id_statut' => 6,
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

?>
