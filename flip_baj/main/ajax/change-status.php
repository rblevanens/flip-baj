<?php

namespace flip_baj\main\ajax;

use PDOException;
use PDO;

include('../pdo_connect.php');
include('../constantes.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log(print_r($_POST, true));
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $jeuId = $_POST['id'];

        try {
            $stmt = $pdo->prepare($SQL_56_ChangeStatut);
            
            $stmt->bindParam(':jeuId', $jeuId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Statut mis à jour avec succès.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du statut.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Identifiant du jeu manquant.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode de requête non autorisée.']);
}
?>
