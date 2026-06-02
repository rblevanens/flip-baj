<?php

namespace App\Utils;

use PDO;
use PDOException;

class Database {
    private static $pdo;

    public static function getInstance() {
        if (self::$pdo === null) {
            // Utiliser les variables d'environnement pour la configuration de la base de données
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'baj';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';

            try {
                self::$pdo = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );
            } catch (PDOException $e) {
                // Ne pas utiliser die() dans une application qui a des endpoints AJAX.
                // Lancer une exception permet au code appelant de la gérer proprement.
                // Par exemple, en retournant une réponse JSON avec un code d'erreur HTTP.
                error_log("Database Connection Error: " . $e->getMessage());
                // Pour le débogage, il peut être utile de voir l'erreur, mais en production,
                // on renverrait une réponse générique.
                http_response_code(500);
                echo json_encode(['error' => 'Erreur de connexion à la base de données.']);
                exit;
            }
        }
        return self::$pdo;
    }
}
