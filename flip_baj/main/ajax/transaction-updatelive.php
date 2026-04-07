<?php
namespace flip_baj\main\ajax;

use PDO;
use PDOException;

include('../constantes.php');

// Vérifier si des données POST sont présentes
if (isset($_POST["type"])) {
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }
    
    // Utilisation d'une connexion persistante à la base de données
    include('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }
    
    // Récupération des données POST
    $type = $_POST["type"];
    $montantTotal = $_POST["montantTotal"];
    $montantPercu = $_POST["montantPercu"];
    $montantRendu = $_POST["montantRendu"];
    $montantDon = $_POST["montantDon"];
    $paiement = $_POST["paiement"];
    $ip = $_POST["ip"];
    $id_transaction = $_POST["id_transaction"];
    
    try {
        // Utilisation d'une requête préparée
        $statement = $pdo->prepare($SQL_23_transactionUpdate);
        if (!$statement) {
            throw new PDOException($pdo->error);
        }
        
        // Exécution de la requête avec les paramètres
        $date = date('Y-m-d H:i:s');
        if (!$statement->execute([
            'type' => $type,
            'montantTotal' => $montantTotal,
            'montantPercu' => $montantPercu,
            'montantRendu' => $montantRendu,
            'montantDon' => $montantDon,
            'paiement' => $paiement,
            'date' => $date,
            'ip' => $ip,
            'id_transaction' => $id_transaction
        ])) {
            throw new PDOException($statement->error);
        }
        
        // Succès de l'exécution de la requête
        echo json_encode([
            "message1" => $date,
            "message2" => '1'
        ]);
    } catch (PDOException $e) {
        // Gestion des erreurs
        echo json_encode([
            "message1" => $e->getMessage(),
            "message2" => '0'
        ]);
    } finally {
        // Fermeture de la connexion et du statement
        $statement = null;
        $pdo = null;
    }
}
?>
