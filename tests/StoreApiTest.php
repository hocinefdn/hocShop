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
     * 2. CRUD : Scénario complet de test du flux nominal
     */
    public function testFullStoreCrudWorkflow(): void
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json'
        ];

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
        $this->assertIsArray($listData['data']);

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
            'postal_code' => '75002', // Changement de code postal
            'city' => 'Paris',
            'is_active' => false // On le passe en inactif pour tester le changement d'état
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

        // --- F. VERIFICATION POST-DELETE (GET 404) ---
        $response = $this->client->request('GET', $this->apiUrl . "/api/stores/{$storeId}", [
            'headers' => $headers
        ]);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * 3. VALIDATION : Test des échecs de contraintes (400 Bad Request)
     */
    public function testCreateStoreValidationFailure(): void
    {
        $response = $this->client->request('POST', $this->apiUrl . '/api/stores', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'name' => '', // Obligatoire -> Erreur
                'address' => 'Adresse de test',
                'postal_code' => '123', // Regex 5 chiffres -> Erreur
                'city' => '' // Obligatoire -> Erreur
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertEquals('error', $data['status']);
        $this->assertArrayHasKey('name', $data['error']);
        $this->assertArrayHasKey('postalCode', $data['error']);
        $this->assertArrayHasKey('city', $data['error']);
    }
}
