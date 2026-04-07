<?php
// Log pour debug
file_put_contents("log.txt", "POST reçu : " . print_r($_POST, true), FILE_APPEND);

// Charger PHPMailer
require 'C:/xampp/htdocs/project/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Connexion BDD
include('../pdo_connect.php');
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fonction d’envoi
function envoyerEmail($email, $prenom, $file_path, $nom_fichier) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'boursejeuxflip@gmail.com';
        $mail->Password = 'gfbs qzpz eoal djie'; // ⚠️ Change ce mot de passe en variable d’environnement si possible
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('boursejeuxflip@gmail.com', 'WOOPY On Off');
        $mail->addAddress($email);

        if (file_exists($file_path)) {
            $mail->addAttachment($file_path, $nom_fichier);
        } else {
            throw new Exception("Fichier non trouvé : $file_path");
        }

        $mail->isHTML(true);
        $mail->Subject = 'Document WoopyOnOff';
        $mail->Body = "Bonjour <b>$prenom</b>,<br><br>Veuillez trouver votre document en pièce jointe.";
        $mail->AltBody = "Bonjour $prenom,\nVeuillez trouver votre document en pièce jointe.";

        $mail->send();
        echo json_encode(["status" => "success", "message" => "Mail envoyé à $email."]);
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Erreur d’envoi : " . $mail->ErrorInfo,
            "exception" => $e->getMessage()
        ]);
    }
    exit;
}

// Si POST contient un ID vendeur
if (isset($_POST['idrestitution'])) {
    $id = intval($_POST['idrestitution']);
    $stmt = $pdo->prepare("SELECT * FROM al_bourse_users WHERE id = ?");
    $stmt->execute([$id]);
    $vendeur = $stmt->fetch(PDO::FETCH_OBJ);

    if ($vendeur) {
        $numero = str_pad($id, 4, '0', STR_PAD_LEFT);
        $nom_fichier = "facture_{$numero}_" . date('d_m_y') . ".pdf";
        $chemin = "../pdf/pdf/facture/$nom_fichier";
        envoyerEmail($vendeur->email, $vendeur->prenom, $chemin, $nom_fichier);
    } else {
        echo json_encode(["status" => "error", "message" => "Aucun vendeur trouvé avec cet ID."]);
    }
    exit;
}

// Si POST contient un ID acheteur
if (isset($_POST['idacheteur'])) {
    $id = intval($_POST['idacheteur']);
    $stmt = $pdo->prepare("SELECT * FROM al_bourse_acheteur WHERE id = ?");
    $stmt->execute([$id]);
    $acheteur = $stmt->fetch(PDO::FETCH_OBJ);

    if ($acheteur) {
        $numero = str_pad($id, 4, '0', STR_PAD_LEFT);
        $nom_fichier = "justificatif_{$numero}_" . date('d_m_y') . ".pdf";
        $chemin = "../pdf/pdf/justificatif/$nom_fichier";
        envoyerEmail($acheteur->email, $acheteur->prenom, $chemin, $nom_fichier);
    } else {
        echo json_encode(["status" => "error", "message" => "Aucun acheteur trouvé avec cet ID."]);
    }
    exit;
}

// Aucun paramètre POST valide
echo json_encode(["status" => "error", "message" => "Aucun paramètre POST valide reçu."]);
exit;
