<?php

use App\Controllers\HomeController;
use App\Controllers\VenteController;
use App\Controllers\VenteDesJeuxController;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Simple router logic
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$basePath = '/baj-flip/flip_baj/public/';

$route = str_replace($basePath, '', $requestPath);

// Intercepte les appels vers /ajax/...
if (strpos($route, 'ajax/') === 0) {
    $ajaxFile = __DIR__ . '/../main/' . $route;
    if (file_exists($ajaxFile)) {
        require $ajaxFile;
    } else {
        http_response_code(404);
        echo "Ajax file not found.";
    }
    exit;
}

// Intercepte les appels vers /Json/... (DataTables i18n)
if (strpos($route, 'Json/') === 0) {
    $jsonFile = __DIR__ . '/../main/' . $route;
    if (file_exists($jsonFile)) {
        header('Content-Type: application/json; charset=utf-8');
        readfile($jsonFile);
    } else {
        http_response_code(404);
        echo "Json file not found.";
    }
    exit;
}

$page = $_GET['page'] ?? 'home';

// partie routeur ($page)
switch ($page) {
    case 'home':
        $controller = new HomeController();
        $controller->index();
        break;

    case 'vente':
        $controller = new VenteController();
        $controller->index();
        break;

    case 'ventedesjeux':
        $controller = new VenteDesJeuxController();
        $controller->index();
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

    // Si on demande une page qui n'existe pas dans ce switch :
    default:
        http_response_code(404);
        echo "<h1>Erreur 404 - Page introuvable</h1>";
        echo "<p>La page demandée n'existe pas ou n'a pas encore été configurée dans le routeur.</p>";
        echo "<a href='?page=home'>Retour à l'accueil</a>";
        break;
}