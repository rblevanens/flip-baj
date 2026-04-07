<?php
namespace flip_baj\main\ajax;

use PDOException, PDO;

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die();
}

include ('../pdo_connect.php');
include ('../constantes.php');

if (is_null($pdo)) {
    die('Could not connect to the database!');
}

// Assurez-vous que l'ID de l'acheteur est fourni
if (isset($_POST['id'])) {
    $acheteurId = $_POST['id'];
    
    try {
        $statement = $pdo->prepare($SQL_9_acheteurget);
        $statement->execute(['acheteurId' => $acheteurId]);
        $acheteur = $statement->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode($acheteur);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur de base de données']);
    }
} else {
    echo json_encode(['error' => 'ID de l\'acheteur manquant']);
}
?>