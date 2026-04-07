<?php
namespace flip_baj\main\ajax;

/**
 * Script de traitement des fichiers CSV pour insertion dans les tables al_bourse_users et al_bourse_liste
 * sans supprimer les données existantes. Ajout uniquement si non-existant.
 */
include '../pdo_connect.php'; // Connexion PDO

$a_json = array(
    'message1' => '',
    'message2' => '1' // 1 = OK, 0 = erreur
);

// Traitement des fichiers soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Traitement du fichier des vendeurs
    if (isset($_FILES["file_user"]) && $_FILES["file_user"]["error"] == UPLOAD_ERR_OK) {
        $file_user = $_FILES["file_user"]["tmp_name"];
        $handle_user = fopen($file_user, "r");

        if ($handle_user !== FALSE) {
            while (($data = fgetcsv($handle_user, 1000, ",")) !== FALSE) {
                $data[0] = preg_replace('/\xef\xbb\xbf/', '', $data[0]);

                // Vérifier l'existence du vendeur
                $checkStmt = $pdo->prepare("SELECT id FROM al_bourse_users WHERE nom = :nom AND prenom = :prenom AND telephone = :telephone");
                $checkStmt->execute([
                    'nom' => $data[1],
                    'prenom' => $data[2],
                    'telephone' => $data[3]
                ]);

                if ($checkStmt->rowCount() == 0) {
                    // Insérer le vendeur s’il n’existe pas
                    $insertStmt = $pdo->prepare("INSERT INTO al_bourse_users (nom, prenom, telephone, email, adresse, code_postal, ville, denomination_sociale, siege_social, attestation_signee) VALUES (:nom, :prenom, :telephone, :email, :adresse, :code_postal, :ville, :denomination_sociale, :siege_social, :attestation_signee)");
                    $insertStmt->execute([
                        'nom' => $data[1],
                        'prenom' => $data[2],
                        'telephone' => $data[3],
                        'email' => $data[4],
                        'adresse' => $data[5],
                        'code_postal' => $data[6],
                        'ville' => $data[7],
                        'denomination_sociale' => $data[8],
                        'siege_social' => $data[9],
                        'attestation_signee' => $data[10]
                    ]);
                }

                $checkStmt->closeCursor();
            }

            fclose($handle_user);
        } else {
            $a_json['message1'] .= "Erreur ouverture fichier CSV vendeurs. ";
            $a_json['message2'] = '0';
        }
    }

    // Traitement du fichier des jeux
    if (isset($_FILES["file_liste"]) && $_FILES["file_liste"]["error"] == UPLOAD_ERR_OK) {
        $file_liste = $_FILES["file_liste"]["tmp_name"];
        $handle_liste = fopen($file_liste, "r");

        if ($handle_liste !== FALSE) {
            while (($data = fgetcsv($handle_liste, 1000, ",")) && $data[2] != '') {
                // data[9]=nom, data[10]=prenom, data[11]=telephone
                $stmt_id = $pdo->prepare("SELECT id FROM al_bourse_users WHERE nom = ? AND prenom = ? AND telephone = ?");
                $stmt_id->execute([$data[9], $data[10], $data[11]]);
                $result = $stmt_id->fetch();

                if (!$result) continue; // Vendeur non trouvé
                $id_utilisateur = $result['id'];

                // Selon la présence de la date
                if (!empty($data[7])) {
                    $stmt_liste = $pdo->prepare("INSERT INTO al_bourse_liste (id_utilisateur, nom_jeu, prix, code_barre, statut, id_depot, date_reception, annee) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt_liste->execute([
                        $id_utilisateur,
                        $data[2],
                        $data[3],
                        $data[4],
                        $data[5],
                        $data[6],
                        $data[7],
                        $data[8]
                    ]);
                } else {
                    $stmt_liste = $pdo->prepare("INSERT INTO al_bourse_liste (id_utilisateur, nom_jeu, prix, code_barre, statut, id_depot, annee) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt_liste->execute([
                        $id_utilisateur,
                        $data[2],
                        $data[3],
                        $data[4],
                        $data[5],
                        $data[6],
                        $data[8]
                    ]);
                }

                $stmt_id->closeCursor();
                $stmt_liste->closeCursor();
            }

            fclose($handle_liste);
        } else {
            $a_json['message1'] .= "Erreur ouverture fichier CSV jeux. ";
            $a_json['message2'] = '0';
        }
    }
}

echo json_encode($a_json);
die();
?>
