<?php
namespace flip_baj\main\ajax;

use PDOException;

include ('../constantes.php');

if (isset($_POST["type"])) {
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }

    include ('../pdo_connect.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }

    $type = $_POST["type"];
    $montantTotal = $_POST["montantTotal"];
    $montantPercu = $_POST["montantPercu"];
    $montantRendu = $_POST["montantRendu"];
    $paiement = isset($_POST["paiement"]) ? $_POST["paiement"] : '';
    $ip = $_POST["ip"];
    $id_acheteur = isset($_POST["id_phpbb_acheteur"]) && $_POST["id_phpbb_acheteur"] !== '' ? $_POST["id_phpbb_acheteur"] : 0;

    // === Sécurité : paiement vide ? On le met par défaut à "especes"
    if (empty($paiement)) {
        $paiement = "espèces";
    }

    try {
        $statement = $pdo->prepare($SQL_22_transactionadd);
    } catch (PDOException $e) {
        echo json_encode([
            "message1" => "Erreur SQL : " . $e->getMessage(),
            "message2" => "0"
        ]);
        die();
    }

    $success = $statement->execute([
        'type' => $type,
        'montantTotal' => $montantTotal,
        'montantPercu' => $montantPercu,
        'montantRendu' => $montantRendu,
        'paiement' => $paiement,
        'date' => date('Y-m-d H:i:s'),
        'id_acheteur' => $id_acheteur,
        'ip' => $ip
    ]);

    if (!$success) {
        echo json_encode([
            "message1" => "Erreur exécution : " . $statement->errorInfo()[2],
            "message2" => "0"
        ]);
    } else {
        echo json_encode([
            "message1" => $pdo->lastInsertId(),
            "message2" => "1"
        ]);
    }

    $statement = null;
    $pdo = null;
}
?>
