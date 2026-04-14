<?php

namespace App\Utils;

use PDO;
use PDOException;

class Database {
    private static $pdo;

    public static function getInstance() {
        if (self::$pdo === null) {
            $user = 'root';
            $pass = '';
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=baj;charset=utf8mb4',
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );
            } catch (PDOException $e) {
                // En environnement de développement, on peut afficher l'erreur.
                // En production, il faudrait logger l'erreur et afficher un message générique.
                die("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
