<?php

namespace App\Core;

use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;

class JsonResponse extends SymfonyJsonResponse
{
    public static function createStandard(mixed $data, int $status = 200, string $statusLabel = 'success'): self
    {
        $payload = [
            'status' => $statusLabel,
            'timestamp' => time(),
        ];

        if ($statusLabel === 'error') {
            $payload['error'] = $data;
        } else {
            $payload['data'] = $data;
        }

        return new self($payload, $status, [
            'Content-Type' => 'application/json; charset=utf-8'
        ]);
    }
}