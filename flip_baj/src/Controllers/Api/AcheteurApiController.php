<?php

namespace App\Controllers\Api;

use App\Models\Acheteur;

class AcheteurApiController {

    public function get() {
        $id = $_POST['id'] ?? null;
        if (!$id) return $this->sendJson(['error' => 'ID manquant']);

        $model = new Acheteur();
        $this->sendJson($model->getById($id));
    }

    public function search() {
        // L'autocomplétion jQuery UI envoie parfois la clé dynamique (ex: $_POST['email'] = 'test@')
        // On cherche quelle clé a été envoyée pour faire la recherche
        $key = key($_POST);
        $term = $_POST[$key] ?? '';

        $model = new Acheteur();
        // On renvoie un tableau direct pour jQuery UI Autocomplete
        $this->sendJson($model->search($term, $key));
    }

    public function save() {
        $model = new Acheteur();
        $result = $model->save($_POST);

        // On renvoie "message2" pour être compatible avec ton vieux JS
        if ($result['success']) {
            $this->sendJson(['message2' => '1', 'action' => $result['action']]);
        } else {
            $this->sendJson(['message2' => '0', 'error' => $result['message']]);
        }
    }

    private function sendJson($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}