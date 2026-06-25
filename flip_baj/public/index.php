<?php

use App\Controllers\HomeController;
use App\Controllers\VenteController;
use App\Controllers\VenteDesJeuxController;
use App\Controllers\VendeurController;
use App\Controllers\SelectionVendeurController;
use App\Controllers\ListeJeuxController;
use App\Controllers\ReceptionController;
use App\Controllers\RestitutionController;
use App\Controllers\Api\JeuApiController;
use App\Controllers\Api\TransactionApiController;
use App\Controllers\Api\UtilisateurApiController;
use App\Controllers\Api\AcheteurApiController;

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
        $controller = new ReceptionController();
        $controller->index();
        break;

    case 'restitution':
        $controller = new RestitutionController();
        $controller->index();
        break;

    case 'stats':
        require __DIR__ . '/../main/stats.php';
        break;

    case 'admin':
        require __DIR__ . '/../main/admin.php';
        break;

    case 'api/checkout':
        $controller = new TransactionApiController();
        $controller->checkout();
        break;

    case 'api/cloturer-restitution':
        $controller = new TransactionApiController();
        $controller->cloturerRestitution();
        break;

    case 'api/get-user':
        $controller = new UtilisateurApiController();
        $controller->getUser();
        break;

    case 'api/get-acheteur':
        $controller = new AcheteurApiController();
        $controller->get();
        break;

    case 'api/search-acheteur':
        $controller = new AcheteurApiController();
        $controller->search();
        break;

    case 'api/save-acheteur':
        $controller = new AcheteurApiController();
        $controller->save();
        break;

    case 'api/check-vendeur':
        $controller = new UtilisateurApiController();
        $controller->checkVendeur();
        break;

    case 'api/save-vendeur':
        $controller = new UtilisateurApiController();
        $controller->saveVendeur();
        break;


    // --- ROUTES JEUX ---
    case 'api/add-jeu':
        $controller = new JeuApiController();
        $controller->addJeu();
        break;
    case 'api/update-jeu':
        $controller = new JeuApiController();
        $controller->updateJeu();
        break;
    case 'api/restituer-jeu':
        $controller = new JeuApiController();
        $controller->restituerJeu();
        break;
    case 'api/check-codebarre':
        $controller = new JeuApiController();
        $controller->checkCodeBarre();
        break;

    // --- ROUTES JEDITABLE (TEXTE BRUT) ---
    case 'api/inline-codebarre':
        $controller = new JeuApiController();
        $controller->inlineCodeBarre();
        break;
    case 'api/inline-prix':
        $controller = new JeuApiController();
        $controller->inlinePrix();
        break;


    // --- ERREUR 404 ---
    default:
        http_response_code(404);
        echo "Désolé, la page '$page' n'existe pas.";
        break;
}