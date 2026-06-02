<?php

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
    return \Symfony\Component\Validator\Validation::createValidatorBuilder()
        ->enableAttributeMapping()
        ->getValidator();
});

// 3. Routage avec Bramus Router
$router = new \Bramus\Router\Router();

// Exemple de Middleware Global ou de groupe pour l'authentification

// $router->before('GET|POST|PUT|DELETE', '/api/.*', function () use ($container) {
//     $request = $container->get(Request::class);
//     $token = $request->headers->get('Authorization');

//     if (!$token || $token !== 'Bearer ' . ($_ENV['API_TOKEN'] ?? 'secret_token')) {
//         throw new \App\Exception\HttpException("Unauthorized", 401);
//     }
// });



// Définition des routes CRUD Magasins
$router->mount('/api/stores', function () use ($router, $container) {

    // GET /api/stores 
    $router->get('/', function () use ($container) {
        (new App\Store\StoreController($container))->index();
    });

    // POST /api/stores 
    $router->post('/', function () use ($container) {
        (new App\Store\StoreController($container))->create();
    });

    // GET /api/stores/{id}
    $router->get('/(\d+)', function ($id) use ($container) {
        (new App\Store\StoreController($container))->show((int)$id);
    });

    // PUT /api/stores/{id} 
    $router->put('/(\d+)', function ($id) use ($container) {
        (new App\Store\StoreController($container))->update((int)$id);
    });

    // DELETE /api/stores/{id} 
    $router->delete('/(\d+)', function ($id) use ($container) {
        (new App\Store\StoreController($container))->delete((int)$id);
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
