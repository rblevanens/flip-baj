<?php

namespace App\Models;

use App\Utils\Database;
use PDO;

class Transaction {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllVentes() {
        $sql = "SELECT 
                    t.id AS id_transaction, 
                    t.date, 
                    t.montantTotal, 
                    t.montantPercu, 
                    t.montantRendu, 
                    t.paiement, 
                    t.id_acheteur, 
                    a.nom, 
                    a.prenom, 
                    a.email,
                    -- Sous-requête pour compter le nombre de jeux dans cette transaction
                    (SELECT COUNT(*) FROM al_bourse_transaction_liste tl WHERE tl.id_transaction = t.id) AS nbjeux
                FROM al_bourse_transactions t
                LEFT JOIN al_bourse_acheteur a ON t.id_acheteur = a.id
                ORDER BY t.date DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}