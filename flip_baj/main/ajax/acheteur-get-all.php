<?php
namespace flip_baj\main\ajax;


use PDO, PDOException;

// Inclure les fichiers nécessaires pour la connexion à la base de données
include ('../pdo_connect.php');
include_once ('../constantes.php');


if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    $pdo = null;
    die();
}

 // Vérifier si la connexion à la base de données est établie
if (is_null($pdo)) {
     die('Could not connect to database!');
}

try {
     $statement = $pdo->query($SQL_57_selectionAcheteurs);
} catch (PDOException $e) {
     echo json_encode(array(
         "message1" => $e->getMessage(),
         "message2" => '0'
     ));
     die();
}

// Préparer les données à renvoyer au format JSON
$recordsTotal = 0;
$data = [];
while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    $data[] = $row;
    $recordsTotal ++;
}

$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;

 // Renvoyer les données au format JSON
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $recordsTotal, // Nombre total de lignes dans la table (à remplacer par le vrai nombre)
    "data" => $data // Données à afficher dans le tableau
]);

 // Fermer la connexion à la base de données
$pdo = null;
?>