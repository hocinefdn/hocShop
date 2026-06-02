<?php

namespace App\User;

use App\Core\Container;
use App\Core\JsonResponse;
use App\Service\AuthService;
use PDO;
use Symfony\Component\HttpFoundation\Request;

class AuthController
{
    private PDO $pdo;
    private Request $request;
    private AuthService $authService;

    public function __construct(Container $container)
    {
        $this->pdo = $container->get(PDO::class);
        $this->request = $container->get(Request::class);
        $this->authService = new AuthService();
    }

    /**
     * POST /api/login
     */
    public function login(): void
    {
        $content = json_decode($this->request->getContent(), true) ?? [];
        $email = $content['email'] ?? '';
        $password = $content['password'] ?? '';

        if (empty($email) || empty($password)) {
            JsonResponse::createStandard(['message' => 'Email et mot de passe requis.'], 400, 'error')->send();
            return;
        }

        // Rechercher l'utilisateur
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Code de debug temporaire
        if (!$user) {
            JsonResponse::createStandard(['message' => 'Utilisateur introuvable en BDD avec cet email.'], 401, 'error')->send();
            return;
        }

        // Vérifier le mot de passe haché
        if (!$user || !password_verify($password, $user['password'])) {
            JsonResponse::createStandard(['message' => 'Identifiants invalides.'], 401, 'error')->send();
            return;
        }

        // Générer le JWT
        $token = $this->authService->generateToken((int)$user['id'], $user['email']);

        JsonResponse::createStandard([
            'message' => 'Connexion réussie.',
            'token' => $token,
            'type' => 'Bearer'
        ], 200)->send();
    }
}
