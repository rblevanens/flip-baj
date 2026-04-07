<?php
namespace flip_baj\main\ajax;

use PDOException;

include ('../constantes.php');

if (isset($_POST["idVendeurEdition"]) || isset($_POST["idVendeurFlip"])) {
    // Sécurité : vérifier requête AJAX
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die("Unauthorized request");
    }

    include('../pdo_connect.php');

    if (is_null($pdo)) {
        die('Could not connect to database!');
    }

    // Récupération des champs
    $nom_jeu = isset($_POST["nom"]) ? $_POST["nom"] : 'Default Game Name'; 
    $idVendeurEdition = isset($_POST["idVendeurEdition"]) ? $_POST["idVendeurEdition"] : ($_POST["idVendeurFlip"] ?? null);
    $codebarre = $_POST["codebarre"] ?? '';
    $statut = $_POST["statut"] ?? '';
    $vigilance = 0;
    $vendu = isset($_POST["vendu"]) ? floatval($_POST["vendu"]) : null;
    $ip = $_POST["ip"] ?? '';

    // Vérification du prix
    if ($vendu === null || $vendu < 0) {
        echo json_encode([
            "message1" => "TEST ERREUR AFFICHAGE",
            "message2" => "0"
        ]);
        exit;
        
    }

    try {
        $statement = $pdo->prepare($SQL_11_insertlistejeu);

        $success = $statement->execute([
            'idVendeurEdition' => $idVendeurEdition,
            'nom_jeu' => $nom_jeu,
            'vendu' => $vendu,
            'codebarre' => $codebarre,
            'statut' => $statut,
            'vigilance' => $vigilance,
            'ip' => $ip,
            'date_reception' => date('Y-m-d H:i:s'),
            'annee' => annee_base
        ]);

        if (!$success) {
            echo json_encode([
                "message1" => $statement->errorInfo()[2] ?? 'Erreur lors de l\'insertion',
                "message2" => '0'
            ]);
        } else {
            $id = $pdo->lastInsertId();

            // Journaliser le changement de statut
            $paramJournal = [
                'id_liste' => $id,
                'old_id_statut' => 11,
                'new_id_statut' => $statut,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'date' => date('Y-m-d H:i:s')
            ];

            try {
                $statementJournal = $pdo->prepare($SQL_30_journalStatut);
                $statementJournal->execute($paramJournal);
            } catch (PDOException $e) {
                echo json_encode([
                    "message3" => $e->getMessage(),
                    "message4" => '0'
                ]);
                exit;
            }

            echo json_encode([
                "message1" => $id,
                "message2" => '1'
            ]);
        }

    } catch (PDOException $e) {
        echo json_encode([
            "message1" => $e->getMessage(),
            "message2" => '0'
        ]);
    }

    $statement = null;
    $pdo = null;
}
?>
