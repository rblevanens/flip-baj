<?php

namespace App\Models;

use App\Utils\Database;
use PDO;

class Vendeur {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllVendeurs() {
        $sql = "SELECT id, nom, prenom, email FROM utilisateurs ORDER BY nom ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}