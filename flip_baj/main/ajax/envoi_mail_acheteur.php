<?php
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'C:/xampp/htdocs/FlipBAJ/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Connexion
$pdo = new PDO("mysql:host=localhost;dbname=baj;charset=utf8", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
]);

$date = date('d_m_y');
$basePath = realpath(__DIR__ . '/../pdf/pdf/justificatif/2025/') . '/';
$envoyes = 0;

// Récupération des acheteurs
$stmt = $pdo->query("SELECT * FROM al_bourse_acheteur");
$acheteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Début HTML
echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'>";
echo "<style>
    body { font-family: Arial, sans-serif; background:#f5f5f5; padding:20px; }
    h2 { color:#333; }
    table { border-collapse: collapse; width: 100%; background:#fff; box-shadow:0 0 10px #ccc; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
    th { background: #28a745; color: white; }
    tr.success { background: #d4edda; }
    tr.error { background: #f8d7da; }
    tr.notfound { background: #fff3cd; }
    .summary { margin-top:20px; font-weight:bold; }
</style>";
echo "<title>Résultat envoi mails acheteurs</title></head><body>";
echo "<h2>📩 Résultat de l'envoi des mails (acheteurs)</h2>";
echo "<table><tr><th>ID</th><th>Nom</th><th>Email</th><th>État</th></tr>";

foreach ($acheteurs as $acheteur) {
    $id = $acheteur['id'];
    $prenom = strtolower($acheteur['prenom']);
    $nom = strtolower($acheteur['nom']);
    $email = $acheteur['email'];

    $numero = str_pad($id, 4, '0', STR_PAD_LEFT);
    $nomFichier = "justificatif_{$nom}_{$numero}_{$date}.pdf";
    $cheminPDF = $basePath . $nomFichier;

    if (!file_exists($cheminPDF)) {
        echo "<tr class='notfound'>
                <td>$id</td>
                <td>$prenom $nom</td>
                <td>$email</td>
                <td>❌ Fichier manquant : <code>$nomFichier</code></td>
              </tr>";
        continue;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'boursejeuxflip@gmail.com';
        $mail->Password   = 'gfbs qzpz eoal djie'; // mot de passe application
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->CharSet = 'UTF-8';
        $mail->setFrom('boursejeuxflip@gmail.com', 'Woopy On Off');
        $mail->addAddress($email, "$prenom $nom");

        $mail->isHTML(true);
        $mail->Subject = 'Votre justificatif d’achat - Bourse aux Jeux';
        $mail->Body    = "Bonjour $prenom,<br><br>Veuillez trouver ci-joint votre justificatif d'achat.<br><br>L'équipe Woopy.";
        $mail->AltBody = "Bonjour $prenom,\nVeuillez trouver ci-joint votre justificatif d'achat.";
        $mail->addAttachment($cheminPDF, $nomFichier);

        $mail->send();

        $urlFichier = "http://localhost/FlipBAJ/flip_baj/main/pdf/pdf/justificatif/2025/" . $nomFichier;

        echo "<tr class='success'>
                <td>$id</td>
                <td>$prenom $nom</td>
                <td>$email</td>
                <td> <a href='$urlFichier' target='_blank'>Justificatif envoyé</a></td>
              </tr>";

        $envoyes++;
    } catch (Exception $e) {
        $erreur = htmlspecialchars($mail->ErrorInfo, ENT_QUOTES, 'UTF-8');
        echo "<tr class='error'>
                <td>$id</td>
                <td>$prenom $nom</td>
                <td>$email</td>
                <td>❌ Erreur envoi : $erreur</td>
              </tr>";
    }
}

echo "</table>";
echo "<div class='summary'> Total des mails envoyés avec succès : <strong>$envoyes</strong></div>";
echo "</body></html>";
