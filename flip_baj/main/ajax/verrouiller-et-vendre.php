<?php
require_once '../pdo_connect.php';
require_once '../constantes.php';

header('Content-Type: application/json');

$id_jeu = isset($_POST['id']) ? intval($_POST['id']) : 0;
$code_barre = isset($_POST['codebarre']) ? $_POST['codebarre'] : '';
$checkOnly = isset($_POST['checkOnly']) && $_POST['checkOnly'] === 'true';

$id_statut_attendu = 5;
$id_statut_final = 3;

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT statut FROM al_bourse_liste WHERE id = :id AND code_barre = :code_barre FOR UPDATE");
    $stmt->execute([
        ':id' => $id_jeu,
        ':code_barre' => $code_barre
    ]);
    $jeu = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$jeu) {
        throw new Exception("Jeu introuvable.");
    }

    if ((int)$jeu['statut'] !== $id_statut_attendu) {
        throw new Exception("Ce jeu a déjà été vendu ou n'est pas disponible.");
    }

    if ($checkOnly) {
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "Jeu dispo"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE al_bourse_liste SET statut = :statut, date_sortie_stock = NOW() WHERE id = :id");
    $stmt->execute([
        ':statut' => $id_statut_final,
        ':id' => $id_jeu
    ]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
