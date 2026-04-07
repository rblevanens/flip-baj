<?php
require_once '../pdo_connect.php';
require_once '../constantes.php';
header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$code = isset($_POST['codebarre']) ? $_POST['codebarre'] : '';

if ($id === 0 || $code === '') {
    echo json_encode(['success' => false, 'message' => ' Paramètres manquants']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE al_bourse_liste
        SET statut = 5
        WHERE id = :id AND code_barre = :code AND statut = 2
    ");
    $stmt->execute([':id' => $id, ':code' => $code]);

    if ($stmt->rowCount() === 1) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => ' Ce jeu n\'est plus en stock (déjà pris par un autre utilisateur).']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => ' Erreur serveur']);
}
