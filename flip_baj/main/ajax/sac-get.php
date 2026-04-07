<?php
namespace flip_baj\main\ajax;

use PDOException;

// Inclure les constantes et le fichier de connexion PDO
include('../constantes.php');
include('../pdo_connect.php');

// Vérifier si la requête est bien une requête AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die();
}

// Initialiser les variables de réponse par défaut
$response = array(
    'message1' => array(), // Message avec les infos à remplir
    'message2' => '0' // 0 = erreur, 1 = succès
);

try {
    // Vérifier que la connexion PDO est établie
    if ($pdo === null) {
        throw new PDOException('Connexion à la base de données échouée.');
    }
    
    // -1 est l'id utilisé pour l'object sac
    // Si besoin de développer l'espace de vente, les id negatifs peuvent être des objects de merch
    $statement = $pdo->prepare($SQL_15_01_get_infos_exemplaire);
    $statement->execute(['id' => '-1']);
    $res = $statement->fetch();

    if ($res) {
        $response['message1'] = array(
            'SAC_ID' => $res['id'],
            'SAC_JEU' => $res['nom_jeu'],
            'SAC_PRIX' => $res['vendu'],
            'SAC_CODEBARRE' => $res['code_barre']
        );
        // Indiquer que tout s'est bien déroulé
        $response['message2'] = '1';
    } else {
        // Aucun résultat trouvé
        $response['message1'] = 'Aucun exemplaire trouvé.';
    }
} catch (PDOException $e) {
    // En cas d'erreur PDO, renvoyer l'erreur dans le message1
    $response['message1'] = 'Erreur PDO : ' . $e->getMessage();
} catch (Exception $e) {
    // En cas d'erreur générale, renvoyer l'erreur dans le message1
    $response['message1'] = 'Erreur : ' . $e->getMessage();
}

// Renvoyer la réponse encodée en JSON
echo json_encode($response);

// Fermer la connexion PDO et libérer les ressources
$pdo = null;
$statement = null;
?>