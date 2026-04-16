<?php
namespace flip_baj\main;

use PDO;

$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=baj;charset=utf8mb4',
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    print "Erreur : " . $e->getMessage() . "<br/>";
    die;
}
?>
