<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    private string $secretKey;

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'default_secret';
    }

    /**
     * Génère un JWT valide pendant 1 heure
     */
    public function generateToken(int $userId, string $email): string
    {
        $issuedAt = time();
        $expire = $issuedAt + 3600; // Expire dans 1 heure

        $payload = [
            'iat' => $issuedAt,  // Date de création
            'exp' => $expire,    // Date d'expiration
            'user_id' => $userId,
            'email' => $email
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    /**
     * Valide et décode un token. Retourne le payload ou null si invalide/expiré
     */
    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null; // Token expiré, signature invalide, etc.
        }
    }
}
