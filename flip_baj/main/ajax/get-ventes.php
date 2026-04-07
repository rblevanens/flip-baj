<?php
namespace flip_baj\main\ajax;

use PDOException, PDO;


if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) or strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die();
}

include ('../pdo_connect.php');
include ('../constantes.php');

if (is_null($pdo)) {
    die('Could not connect to the database!');
}

try {
    $statement = $pdo->prepare($SQL_25_getlistevente);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

$statement->execute();

$data = $statement->fetchAll(PDO::FETCH_ASSOC);
$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if ($json === false) {
    header('Content-Type: text/plain');
    echo "Erreur JSON : " . json_last_error_msg();
    exit;
}

header('Content-Type: application/json');
echo $json;
 
?>
