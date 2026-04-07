<?php
namespace flip_baj\main\ajax;

use PDOException;

include ('../constantes.php');

if (isset($_POST["nom"])) {
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }
    
    include ('../pdo_connect.php');
    
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $adresse = $_POST['adresse'];
    $code_postal = $_POST['code_postal'];
    $ville = $_POST['ville'];
    $raison_sociale = $_POST['raison_sociale'];
    $siret = $_POST['siret'];
    $id_transaction = $_POST['id_transaction'];
    
    $params = array(
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'adresse' => $adresse,
        'code_postal' => $code_postal,
        'ville' => $ville,
        'raison_sociale' => $raison_sociale,
        'siret' => $siret
    );
    
    try {
        $statement = $pdo->prepare($SQL_32_acheteuradd);
    } catch (PDOException $e) {
        echo json_encode(array(
            "message1" => $e->getMessage(),
            "message2" => '0'
        ));
        die();
    }
    
    if (!$statement || !$statement->execute($params)) {
        echo json_encode(array(
            "message1" => $pdo->errorInfo()[2],
            "message2" => '0'
        ));
    } else {
        $id_acheteur = $pdo->lastInsertId();
        
        try {
            $statement = $pdo->prepare($SQL_34_acheteurassoc);
        } catch (PDOException $e) {
            echo json_encode(array(
                "message1" => $id_acheteur,
                "message2" => '1',
                "message3" => $e->getMessage(),
                "message4" => '0'
            ));
            die();
        }
        
        if (!$statement || !$statement->execute([
            "id_acheteur" => $id_acheteur,
            "id_transaction" => $id_transaction
        ])) {
            echo json_encode(array(
                "message1" => $id_acheteur,
                "message2" => '1',
                "message3" => $pdo->errorInfo()[2],
                "message4" => '0'
            ));
        } else {
            echo json_encode(array(
                "message1" => $id_acheteur,
                "message2" => '1',
                "message3" => 'Transaction modifiée',
                "message4" => '1'
            ));
        }
    }
    
    $statement = null;
    $pdo = null;
}
?>