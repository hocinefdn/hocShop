<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StoreApiTest extends TestCase
{
    private HttpClientInterface $client;
    private string $apiUrl;
    private string $token;

    protected function setUp(): void
    {
        $this->client = HttpClient::create();
        $this->apiUrl = $_ENV['API_URL'] ?? 'http://nginx:80';
        $this->token = $_ENV['API_TOKEN'] ?? 'wshop_secret_token_2026';
    }


    /**
     * Test la création réussie d'un magasin avec validation et authentification
     */
    public function testCreateStoreSuccess(): void
    {
        $payload = [
            'name' => 'Magasin Test PHPUnit',
            'address' => '45 Rue des Tests',
            'postal_code' => '59000',
            'city' => 'Lille',
            'is_active' => true
        ];

        $response = $this->client->request('POST', $this->apiUrl . '/api/stores', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json'
            ],
            'json' => $payload
        ]);

        // On attend un code 201 Created
        $this->assertEquals(201, $response->getStatusCode());

        $data = $response->toArray();
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('id', $data['data']);
        $this->assertEquals('Magasin Test PHPUnit', $data['data']['name']);
    }
}
