<?php
namespace flip_baj\main\ajax;

use PDOException;

include ('../constantes.php');

if (isset($_POST["prenom"], $_POST["nom"], $_POST["telephone"])) {

    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        die();
    }

    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }

    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $telephone = $_POST["telephone"];
    $id = $_POST["id"] ?? '-1';

    // Vérification directe côté serveur
    $sql = "SELECT id FROM al_bourse_users WHERE nom=:nom AND prenom=:prenom AND telephone=:telephone AND id != :id LIMIT 1";
    try {
        $statement = $pdo->prepare($sql);
        $statement->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
            'id' => $id
        ]);

        if ($statement->fetch()) {
            echo json_encode([
                "message1" => 'Un vendeur avec ce nom, prénom et téléphone existe déjà !',
                "message2" => '0'
            ]);
        } else {
            echo json_encode([
                "message1" => '',
                "message2" => '1'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "message1" => 'Erreur serveur : ' . $e->getMessage(),
            "message2" => '0'
        ]);
    }

    $statement = null;
    $pdo = null;
}
?>
