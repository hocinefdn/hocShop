<?php

// FORCE PHP À TOUT AFFICHER À L'ÉCRAN
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Container;
use App\Core\ExceptionHandler;
use App\Core\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Dotenv\Dotenv;

// 1. Initialisation de la sécurité et des variables d'environnement
ExceptionHandler::register();

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// 2. Initialisation du Container DI
$container = new Container();

// Injection de l'objet Request global de Symfony
$container->set(Request::class, function () {
    return Request::createFromGlobals();
});

// Injection de PDO
$container->set(\PDO::class, function () {
    $dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8mb4", $_ENV['DB_HOST'] ?? '127.0.0.1', $_ENV['DB_NAME'] ?? 'wshop_db');
    return new \PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASS'] ?? 'root', [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ]);
});

// Injection du Symfony Validator
$container->set(\Symfony\Component\Validator\Validator\ValidatorInterface::class, function () {
    return \Symfony\Component\Validator\Validation::createValidator();
});

// 3. Routage avec Bramus Router
$router = new \Bramus\Router\Router();

// Exemple de Middleware Global ou de groupe pour l'authentification (Étape 3)
// $router->before('GET|POST|PUT|DELETE', '/api/.*', function () use ($container) {
//     $request = $container->get(Request::class);
//     $token = $request->headers->get('Authorization');

//     if (!$token || $token !== 'Bearer ' . ($_ENV['API_TOKEN'] ?? 'secret_token')) {
//         throw new \App\Exception\HttpException("Unauthorized", 401);
//     }
// });


$router->get('/api/hello', function () use ($container) {

    // Affiche les détails de la requête pour vérification

    $response = JsonResponse::createStandard([
        'message' => 'Hello World!',
        'status' => 'API is running smoothly'
    ], 200);

    $response->send();
});

// Définition des routes CRUD Magasins
$router->mount('/api/stores', function () use ($router, $container) {

    // Route de test Hello World
    $router->get('hello', function () use ($container) {
        // On utilise directement notre réponse standardisée
        $response = JsonResponse::createStandard([
            'message' => 'Hello World!',
            'status' => 'API is running smoothly'
        ], 200);

        $response->send();
    });
});

// Route par défaut pour la racine /
$router->get('/', function () {
    $response = JsonResponse::createStandard([
        'message' => 'Bienvenue sur l\'API WSHOP.',
        'documentation' => 'Consultez le README pour voir les endpoints disponibles.'
    ], 200);
    $response->send();
});

// Run le routeur
$router->run();
