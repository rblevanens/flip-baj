<?php
namespace flip_baj\main\ajax;


use PDOException;

include ('../constantes.php');
if (isset($_POST["idVendeurEdition"])) {
    if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }
    $id = $_POST["idVendeurEdition"];
    // error_log("id:".$id, 0);

    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }
    $nom = $_POST["nomVendeurACreer"];
    $prenom = $_POST["prenomVendeurACreer"];
    $email = $_POST["emailVendeurACreer"];
    $telephone = $_POST["telephoneVendeurACreer"];
    $adresse = $_POST["adresseVendeurACreer"];
    $codepostal = $_POST["codepostalVendeurACreer"];
    $ville = $_POST["villeVendeurACreer"];
    $denomination_sociale = $_POST["denomination_socialeVendeurACreer"];
    $siege_social = $_POST["siege_socialVendeurACreer"];
    $attestation_signee = $_POST["attestation_signeeVendeurACreer"];

    try {
        $statement = $pdo->prepare($SQL_37_01_al_bourse_users_update);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    if (! $statement) {
        echo json_encode(array(
            "message1" => $pdo->errorInfo()[2],
            "message2" => '0'
        ));
    }
    if (! $statement->execute([
        'nom' => $nom,
        'prenom' => $prenom,
        'telephone' => $telephone,
        'email' => $email,
        'adresse' => $adresse,
        'code_postal' => $codepostal,
        'ville' => $ville,
        'denomination_sociale' => $denomination_sociale,
        'siege_social' => $siege_social,
        'attestation_signee' => $attestation_signee,
        'id_vendeur' => $id
    ])) {
        echo json_encode(array(
            "message1" => $statement->errorInfo()[2],
            "message2" => '0'
        ));
    }
    echo json_encode(array(
        "message1" => $id,
        "message2" => '1'
    ));
    $statement = null;
    $pdo = null;
}
?>