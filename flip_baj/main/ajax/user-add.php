<?php
namespace flip_baj\main\ajax;

use PDOException;

include ('../constantes.php');

if (isset($_POST["nomVendeurACreer"])) {
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }

    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }

    // Récupération des données du formulaire
    $nom = trim($_POST["nomVendeurACreer"]);
    $prenom = trim($_POST["prenomVendeurACreer"]);
    $email = trim($_POST["emailVendeurACreer"]);
    $telephone = trim($_POST["telephoneVendeurACreer"]);
    $adresse = trim($_POST["adresseVendeurACreer"]);
    $codepostal = trim($_POST["codepostalVendeurACreer"]);
    $ville = trim($_POST["villeVendeurACreer"]);
    $denomination_sociale = trim($_POST["denomination_socialeVendeurACreer"]);
    $siege_social = trim($_POST["siege_socialVendeurACreer"]);
    $attestation_signee = $_POST["attestation_signeeVendeurACreer"];

    // Validation des champs obligatoires
    $erreurs = [];
    if (empty($nom)) $erreurs[] = "Le nom est obligatoire";
    if (empty($prenom)) $erreurs[] = "Le prénom est obligatoire";
    if (empty($email)) $erreurs[] = "L'email est obligatoire";
    if (empty($adresse)) $erreurs[] = "L'adresse est obligatoire";
    if (empty($ville)) $erreurs[] = "La ville est obligatoire";
    if (empty($codepostal)) $erreurs[] = "Le code postal est obligatoire";

    // Si des erreurs sont présentes, on arrête le traitement
    if (!empty($erreurs)) {
        echo json_encode([
            "message1" => implode("\n", $erreurs),
            "message2" => "0"
        ]);
        exit;
    }

    //  Vérifier si le vendeur existe déjà (nom + prénom + téléphone)
    $checkSQL = "SELECT id FROM al_bourse_users WHERE nom = :nom AND prenom = :prenom AND telephone = :telephone";
    try {
        $checkStmt = $pdo->prepare($checkSQL);
        $checkStmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone
        ]);

        if ($checkStmt->fetch()) {
            echo json_encode([
                "message1" => "Un vendeur avec ce nom, prénom et téléphone existe déjà !",
                "message2" => "0"
            ]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode([
            "message1" => "Erreur lors de la vérification : " . $e->getMessage(),
            "message2" => "0"
        ]);
        exit;
    }

    //  Insérer le nouveau vendeur s'il n'existe pas
    try {
        $statement = $pdo->prepare($SQL_37_user_add);
        $success = $statement->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
            'email' => $email,
            'adresse' => $adresse,
            'code_postal' => $codepostal,
            'ville' => $ville,
            'denomination_sociale' => $denomination_sociale,
            'siege_social' => $siege_social,
            'attestation_signee' => $attestation_signee
        ]);

        if ($success) {
            echo json_encode([
                "message1" => $pdo->lastInsertId(),
                "message2" => "1"
            ]);
        } else {
            echo json_encode([
                "message1" => $statement->errorInfo()[2],
                "message2" => "0"
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "message1" => "Erreur lors de l'insertion : " . $e->getMessage(),
            "message2" => "0"
        ]);
    }

    $statement = null;
    $pdo = null;
}
?>