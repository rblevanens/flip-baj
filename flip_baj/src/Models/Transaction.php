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
                    (SELECT COUNT(*) FROM al_bourse_transaction_liste tl WHERE tl.id_transaction = t.id) AS nbjeux,
                    GROUP_CONCAT(j.nom_jeu SEPARATOR ', ') AS jeux
                FROM al_bourse_transactions t
                LEFT JOIN al_bourse_acheteur a ON t.id_acheteur = a.id
                LEFT JOIN al_bourse_transaction_liste tl ON t.id = tl.id_transaction
                LEFT JOIN al_bourse_jeux j ON tl.id_jeu = j.id
                GROUP BY t.id
                ORDER BY t.date DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}