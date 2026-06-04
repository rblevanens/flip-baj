<?php

namespace App\Models;

use App\Utils\Database;
use PDO;

class Acheteur {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getById($id) {
        $sql = "SELECT * FROM al_bourse_acheteur WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function search($term, $key = 'nom') {
        // Sécurise le nom de la colonne de recherche
        $allowedKeys = ['nom', 'prenom', 'email'];
        if (!in_array($key, $allowedKeys)) {
            $key = 'nom';
        }

        $sql = "SELECT * FROM al_bourse_acheteur 
                WHERE $key LIKE :term 
                ORDER BY nom ASC, prenom ASC 
                LIMIT 15";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['term' => '%' . $term . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save($data) {
        try {
            if (!empty($data['id_acheteur'])) {
                // Modification
                $sql = "UPDATE al_bourse_acheteur 
                        SET nom = :nom, prenom = :prenom, email = :email, adresse = :adresse, 
                            code_postal = :cp, ville = :ville, raison_sociale = :rs, siret = :siret 
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'nom' => $data['nom'], 'prenom' => $data['prenom'], 'email' => $data['email'],
                    'adresse' => $data['adresse'], 'cp' => $data['code_postal'], 'ville' => $data['ville'],
                    'rs' => $data['raison_sociale'], 'siret' => $data['siret'], 'id' => $data['id_acheteur']
                ]);
                return ['success' => true, 'action' => 'updated'];
            } else {
                // Création
                $sql = "INSERT INTO al_bourse_acheteur (nom, prenom, email, adresse, code_postal, ville, raison_sociale, siret) 
                        VALUES (:nom, :prenom, :email, :adresse, :cp, :ville, :rs, :siret)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'nom' => $data['nom'], 'prenom' => $data['prenom'], 'email' => $data['email'],
                    'adresse' => $data['adresse'], 'cp' => $data['code_postal'], 'ville' => $data['ville'],
                    'rs' => $data['raison_sociale'], 'siret' => $data['siret']
                ]);
                return ['success' => true, 'action' => 'created', 'id' => $this->db->lastInsertId()];
            }
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}