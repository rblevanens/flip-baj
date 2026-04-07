<?php
namespace flip_baj\main\ajax;

use PDOException;

include ('../constantes.php');
include ('../pdo_connect.php');
if (is_null($pdo)) {
    die('Could not connect to database!');
}

$idAcheteur = filter_var($_POST["idAcheteur"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
$emailAcheteur = filter_var($_POST["emailAcheteur"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
$nomAcheteur = filter_var($_POST["nomAcheteur"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
$prenomAcheteur = filter_var($_POST["prenomAcheteur"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
$adresseAcheteur = filter_var($_POST["adresseAcheteur"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
$codePostalAcheteur = filter_var($_POST["codePostalAcheteur"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
$villeAcheteur = filter_var($_POST["villeAcheteur"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
$idTransaction = filter_var($_POST["idTransaction"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

if ($idAcheteur == 0) {
    // Insertion du nouvel acheteur
    $sql =$SQL_39_creation_acheteur;
    try {
        $statement = $pdo->prepare($sql);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $statement->execute([
        'nom' => $nomAcheteur,
        'prenom' => $prenomAcheteur,
        'email' => $emailAcheteur,
        'adresse' => $adresseAcheteur,
        'code_postal' => $codePostalAcheteur,
        'ville' => $villeAcheteur
    ]);

    // Récupération de l'id du nouvel utilisateur
    $sql = "SELECT 
            id 
        FROM al_bourse_users 
        ORDER BY id desc LIMIT 1";
    try {
        $statement = $pdo->prepare($sql);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $statement->execute();
    $id = $statement->fetch();
    $idAcheteur = - $id['id'];
} else {
    // Mise à jour des infos acheteur
    $sql = $SQL_40_modification_acheteur;
    try {
        $statement = $pdo->prepare($sql);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $statement->execute([
        'nom' => $nomAcheteur,
        'prenom' => $prenomAcheteur,
        'email' => $emailAcheteur,
        'adresse' => $adresseAcheteur,
        'code_postal' => $codePostalAcheteur,
        'ville' => $villeAcheteur,
        'id_acheteur' => $idAcheteur
    ]);
}

// Mise à jour de la transaction
$sql = $SQL_41_attribution_acheteur_transaction;
try {
    $statement = $pdo->prepare($sql);
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}
$statement->execute([
    'id_acheteur' => $idAcheteur,
    'id_transaction' => $idTransaction
]);
$statement = null;
$pdo = null;

?>
