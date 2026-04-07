<?php
namespace flip_baj\main\pdf;

use PDOException;

include ('../pdo_connect.php');
include ('../constantes.php');
require ('generer_facture.php');
require ('generer_justificatif.php');

if (is_null($pdo)) {
    die(' Connexion à la base de données échouée.');
}

function afficherMessage($id, $nom, $prenom, $type, $cheminWeb) {
    echo " " . sprintf("%04d", $id) . " - {$prenom} {$nom} : {$type} généré → 
          <a href='{$cheminWeb}' target='_blank'>Voir le fichier</a><br />";
}

function getNomPrenomUtilisateur($pdo, $id) {
    $stmt = $pdo->prepare("SELECT nom, prenom FROM al_bourse_users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getNomPrenomAcheteur($pdo, $id) {
    $stmt = $pdo->prepare("SELECT nom, prenom FROM al_bourse_acheteur WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// URL de base accessible depuis le navigateur
$baseUrl = "http://localhost/FlipBAJ/flip_baj/main/pdf/pdf";

echo "<h3 style='color:blue;'>📦 Factures des vendeurs générées :</h3>";
try {
    $sql = "SELECT DISTINCT id_utilisateur FROM al_bourse_liste 
            WHERE id_utilisateur > 0 
              AND statut IN (2,3,4,5,6,7) 
              AND annee = " . annee_base;
    $statement = $pdo->prepare($sql);
    $statement->execute();
    $vendeurs = $statement->fetchAll();

    echo "Il y a " . count($vendeurs) . " vendeurs<br /><br />";
    foreach ($vendeurs as $res) {
        $id_vendeur = $res["id_utilisateur"];
        $infos = getNomPrenomUtilisateur($pdo, $id_vendeur);
        $nom = $infos["nom"] ?? "Inconnu";
        $prenom = $infos["prenom"] ?? "";

        // Générer la facture
        GenererFacture($id_vendeur);

        // Construire le nom du fichier
        $id_formate = sprintf("%04d", $id_vendeur);
        $date_fichier = date("d_m_y");
        $annee = annee_base;
        $nom_fichier = "facture_" . strtolower($nom) . "_" . $id_formate . "_" . $date_fichier . ".pdf";

        // Construire le chemin web
        $cheminWeb = "$baseUrl/facture/$annee/$nom_fichier";

        afficherMessage($id_vendeur, $nom, $prenom, "Facture", $cheminWeb);
    }
} catch (PDOException $e) {
    echo " Erreur SQL vendeurs : " . $e->getMessage() . "<br />";
}

echo "<br /><h3 style='color:blue;'>🧾 Justificatifs des acheteurs générés :</h3>";
try {
    $sql = "SELECT DISTINCT id_acheteur FROM al_bourse_transactions 
            WHERE type='vente' 
              AND id_acheteur != 0 
              AND YEAR(date) = " . annee_base;
    $statement = $pdo->prepare($sql);
    $statement->execute();
    $acheteurs = $statement->fetchAll();

    echo "Il y a " . count($acheteurs) . " acheteurs<br /><br />";
    foreach ($acheteurs as $res) {
        $id_acheteur = $res["id_acheteur"];
        $infos = getNomPrenomAcheteur($pdo, $id_acheteur);
        $nom = $infos["nom"] ?? "Inconnu";
        $prenom = $infos["prenom"] ?? "";

        // Générer le justificatif
        GenererJustificatif($id_acheteur);

        // Construire le nom du fichier
        $id_formate = sprintf("%04d", $id_acheteur);
        $date_fichier = date("d_m_y");
        $annee = annee_base;
        $nom_fichier = "justificatif_" . strtolower($nom) . "_" . $id_formate . "_" . $date_fichier . ".pdf";

        // Construire le chemin web
        $cheminWeb = "$baseUrl/justificatif/$annee/$nom_fichier";

        afficherMessage($id_acheteur, $nom, $prenom, "Justificatif", $cheminWeb);
    }
} catch (PDOException $e) {
    echo " Erreur SQL acheteurs : " . $e->getMessage() . "<br />";
}

echo "<br /><h3 style='text-align:center;color:green;'> Toutes les factures et justificatifs ont été générés avec succès.</h3>";
echo "<p style='text-align:center;margin-top:20px;'><a class='btn btn-primary' href='../admin.php'>Retour à l'administration</a></p>";
exit();
?>
