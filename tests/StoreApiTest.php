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

        // Dynamic authentication step to acquire a valid JWT for the middleware
        $this->token = $this->authenticate();
    }

    /**
     * Helper method to login and retrieve a valid dynamic JWT token
     */
    private function authenticate(): string
    {
        $response = $this->client->request('POST', $this->apiUrl . '/api/login', [
            'json' => [
                'email' => 'hocine@wshop.fr',
                'password' => 'password123'
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->fail("Authentication failed for functional tests. Status code: " . $response->getStatusCode());
        }

        $data = $response->toArray();
        return $data['data']['token'];
    }

    /**
     * Helper to build standard authentication headers
     */
    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * 1. SECURITY: Ensure access is blocked without a token
     */
    public function testGetStoresWithoutTokenReturns401(): void
    {
        $response = $this->client->request('GET', $this->apiUrl . '/api/stores');
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * 2. CRUD: Full nominal workflow validation
     */
    public function testFullStoreCrudWorkflow(): void
    {
        $headers = $this->getHeaders();

        // --- A. CREATE (POST) ---
        $payload = [
            'name' => 'Magasin CRUD PHPUnit',
            'address' => '99 Rue du Code',
            'postal_code' => '75001',
            'city' => 'Paris',
            'is_active' => true
        ];

        $response = $this->client->request('POST', $this->apiUrl . '/api/stores', [
            'headers' => $headers,
            'json' => $payload
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $createData = $response->toArray();
        $storeId = $createData['data']['id'];

        $this->assertNotNull($storeId);
        $this->assertEquals('Magasin CRUD PHPUnit', $createData['data']['name']);

        // --- B. READ LIST (GET ALL) ---
        $response = $this->client->request('GET', $this->apiUrl . '/api/stores', [
            'headers' => $headers
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $listData = $response->toArray();
        $this->assertIsArray($listData['data']['stores']);

        // --- C. READ SINGLE (GET BY ID) ---
        $response = $this->client->request('GET', $this->apiUrl . "/api/stores/{$storeId}", [
            'headers' => $headers
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $singleData = $response->toArray();
        $this->assertEquals($storeId, $singleData['data']['id']);

        // --- D. UPDATE (PUT) ---
        $updatePayload = [
            'name' => 'Magasin CRUD Modifié',
            'address' => '99 Rue du Code',
            'postal_code' => '75002',
            'city' => 'Paris',
            'is_active' => false
        ];

        $response = $this->client->request('PUT', $this->apiUrl . "/api/stores/{$storeId}", [
            'headers' => $headers,
            'json' => $updatePayload
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $updateData = $response->toArray();
        $this->assertEquals('Magasin CRUD Modifié', $updateData['data']['name']);
        $this->assertEquals('75002', $updateData['data']['postal_code']);
        $this->assertFalse($updateData['data']['is_active']);

        // --- E. DELETE (DELETE) ---
        $response = $this->client->request('DELETE', $this->apiUrl . "/api/stores/{$storeId}", [
            'headers' => $headers
        ]);
        $this->assertEquals(200, $response->getStatusCode());

        // --- F. POST-DELETE VERIFICATION (GET 404) ---
        $response = $this->client->request('GET', $this->apiUrl . "/api/stores/{$storeId}", [
            'headers' => $headers
        ]);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * 3. VALIDATION: Check constraint handling failures (400 Bad Request)
     */
    public function testCreateStoreValidationFailure(): void
    {
        $response = $this->client->request('POST', $this->apiUrl . '/api/stores', [
            'headers' => $this->getHeaders(),
            'json' => [
                'name' => '',
                'address' => 'Test Address',
                'postal_code' => '123',
                'city' => ''
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertEquals('error', $data['status']);

        // 💡 In case of a 400 error, Symfony Validator errors are usually mapped 
        // directly inside the 'error' key or 'data' key. Let's target the correct field:
        $errorTarget = $data['error'] ?? $data['data'] ?? [];

        $this->assertArrayHasKey('name', $errorTarget);
        $this->assertArrayHasKey('postalCode', $errorTarget);
        $this->assertArrayHasKey('city', $errorTarget);
    }
}
