<?php

use App\Controllers\HomeController;
use App\Controllers\VenteController;
use App\Controllers\VenteDesJeuxController;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

$basePath = '/baj-flip/flip_baj/public/';
$route = str_replace($basePath, '', $requestPath);
$route = trim($route, '/');

if (strpos($route, 'ajax/') === 0 || strpos($route, 'Json/') === 0) {
    $file = __DIR__ . '/../main/' . $route;
    if (file_exists($file)) {
        if (strpos($route, 'Json/') === 0) header('Content-Type: application/json');
        require $file;
    } else {
        http_response_code(404);
        echo "Ressource introuvable.";
    }
    exit;
}

$page = $route ?: 'home';

switch ($page) {
    case 'home':
        $controller = new HomeController();
        $controller->index();
        break;

    case 'vente':
        $controller = new VenteController();
        $controller->index();
        break;

    case 'api/ventes':
        $controller = new \App\Controllers\VenteController();
        $controller->getVentesAjax();
        break;

    case 'ventedesjeux':
        $controller = new VenteDesJeuxController();
        $controller->index();
        break;

    case 'selectionvendeur':
        require __DIR__ . '/../main/selectionvendeur.php';
        break;

    case 'reception':
        require __DIR__ . '/../main/receptionjeux.php';
        break;

    case 'restitution':
        require __DIR__ . '/../main/restitutiondesjeux.php';
        break;

    case 'listejeux':
        require __DIR__ . '/../main/listejeux.php';
        break;

    case 'stats':
        require __DIR__ . '/../main/stats.php';
        break;

    case 'admin':
        require __DIR__ . '/../main/admin.php';
        break;

    // --- ERREUR 404 ---
    default:
        http_response_code(404);
        echo "Désolé, la page '$page' n'existe pas.";
        break;
}