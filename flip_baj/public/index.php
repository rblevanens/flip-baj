<?php

use App\Controllers\HomeController;
use App\Controllers\VenteController;
use App\Controllers\VenteDesJeuxController;
use App\Controllers\VendeurController;
use App\Controllers\SelectionVendeurController;
use App\Controllers\ListeJeuxController;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

define('URL_BASE', '/baj-flip/flip_baj/public/index.php');

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/baj-flip/flip_baj/public/';

$route = str_replace($basePath, '', $requestPath);
$route = str_replace('index.php', '', $route);
$route = trim($route, '/');

if (strpos($route, 'ajax/') === 0 || strpos($route, 'Json/') === 0) {
    $file = __DIR__ . '/../main/' . $route;
    if (file_exists($file)) {
        if (strpos($route, 'Json/') === 0) {
            header('Content-Type: application/json');
            readfile($file);
        } else {
            require $file;
        }
    } else {
        http_response_code(404);
        echo "Ressource introuvable.";
    }
    exit;
}

$page = $_GET['page'] ?? ($route ?: 'home');

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
        $controller = new VenteController();
        $controller->getVentesAjax();
        break;

    case 'ventedesjeux':
        $controller = new VenteDesJeuxController();
        $controller->index();
        break;

    case 'api/vendeurs':
        $controller = new VendeurController();
        $controller->getVendeursAjax();
        break;

    case 'selectionvendeur':
        $controller = new SelectionVendeurController();
        $controller->index();
        break;

    case 'listejeux':
        $controller = new ListeJeuxController();
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

    // --- ERREUR 404 ---
    default:
        http_response_code(404);
        echo "Désolé, la page '$page' n'existe pas.";
        break;
}