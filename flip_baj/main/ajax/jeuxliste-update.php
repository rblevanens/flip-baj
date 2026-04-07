<?php
namespace flip_baj\main\ajax;

use Exception;
use function flip_baj\main\VerifCodeBarre;

include ('../constantes.php');
include ('../utils.php');

// Vérifie si des données POST sont présentes
if (isset($_POST["id"])) {
    // Vérifie si la requête est une requête AJAX
    if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }

    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to the database!');
    }

    $id = $_POST["id"];
    $statut = $_POST["statut"];
    $codebarre = '';

    // Vérifie si le code barre est présent dans les données POST
    if (isset($_POST["codebarre"])) {
        $codebarre = $_POST["codebarre"];
    }
    // Tente de récupérer le code barre depuis un attribut nommé "value" (si on vient d'une colonne éditable)
    elseif (isset($_POST["value"])) {
            $codebarre = VerifCodeBarre($_POST["value"]);
    }
    

    try {
        // Début de la transaction
        $pdo->beginTransaction();
        
        // Requête de mise à jour du statut
        $sql = $SQL_13_updatestatutlistejeu . $SQL_13_01_code;
        
        // Paramètres communs à toutes les situations
        $param = array(
            'statut' => $statut,
            'code_barre' => $codebarre,
            'id' => $id
        );
        
        // Ajoute les clauses et paramètres supplémentaires en fonction des éléments présents dans le POST
        if (isset($_POST["date"])) {
            $sql .= $SQL_13_04_datesortie;
            $param['date_sortie'] = isset($_POST["date_vente"]) ? $_POST["date_vente"] : date('Y-m-d H:i:s');
        } elseif (isset($_POST['recep'])) {
            $sql .= $SQL_13_02_iddepot;
            $param['id_depot'] = $_SERVER['REMOTE_ADDR'];
            $param['date_reception'] = date('Y-m-d H:i:s');
        }
        
        // Ajoute la clause WHERE à la requête
        $sql .= $SQL_13_05_whereid;
        
        $statement = $pdo->prepare($sql);
        
        if (! $statement) {
            throw new Exception($pdo->error);
        }
        
        if (! $statement->execute($param)) {
            throw new Exception($statement->error);
        }
        
        // Insertion dans le journal des statuts
        $paramJournal = array(
            'id_liste' => $id,
            'old_id_statut' => isset($_POST["old_id_statut"]) ? $_POST["old_id_statut"] : 0,
            'new_id_statut' => $statut,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'date' => date('Y-m-d H:i:s')
        );
        
        $statementJournal = $pdo->prepare($SQL_30_journalStatut);
        
        if (! $statementJournal) {
            throw new Exception($pdo->error);
        }
        
        if (! $statementJournal->execute($paramJournal)) {
            throw new Exception($statementJournal->error);
        }
        
        // Validation de la transaction
        $pdo->commit();
        
        // Succès de la mise à jour
        echo json_encode(array(
            "message1" => $statement->rowCount(),
            "message2" => '1'
        ));
    } catch (Exception $e) {
        // En cas d'erreur, annulation de la transaction
        $pdo->rollback();
        
        // Gestion des erreurs
        echo json_encode(array(
            "message1" => $e->getMessage(),
            "message2" => '0'
        ));
    }
    
}

// Fermeture des connexions
$statement = null;
$pdo = null;
?>