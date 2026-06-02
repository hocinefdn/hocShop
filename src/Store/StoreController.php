<?php

namespace App\Store;

use App\Core\Container;
use App\Core\JsonResponse;
use App\Store\DTO\StoreCreateInput;
use App\Store\DTO\StoreUpdateInput;
use App\Store\DTO\StoreResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StoreController
{
    private StoreService $storeService;
    private Request $request;

    public function __construct(Container $container)
    {
        $repository = new StoreRepository($container->get(\PDO::class));
        $validator = $container->get(ValidatorInterface::class);

        $this->storeService = new StoreService($repository, $validator);
        $this->request = $container->get(Request::class);
    }

    /**
     * GET /api/stores
     */
    public function index(): void
    {
        $sort = $this->request->query->get('sort', 'id');
        $direction = $this->request->query->get('direction', 'ASC');

        $filters = array_filter([
            'city' => $this->request->query->get('city'),
            'is_active' => $this->request->query->get('is_active'),
            'postal_code' => $this->request->query->get('postal_code'),
        ], fn($value) => $value !== null);

        $stores = $this->storeService->getStoreList($filters, $sort, $direction);

        // Map collection using the output DTO layout
        $formattedStores = StoreResponse::fromCollection($stores);

        JsonResponse::createStandard([
            'count' => count($formattedStores),
            'stores' => $formattedStores
        ], 200)->send();
    }

    /**
     * POST /api/stores
     */
    public function create(): void
    {
        $content = json_decode($this->request->getContent(), true) ?? [];

        try {
            $dto = new StoreCreateInput($content);
            $savedStore = $this->storeService->createStore($dto);

            // Format output structure using StoreResponse
            $response = new StoreResponse($savedStore);
            JsonResponse::createStandard($response->toArray(), 201)->send();
        } catch (\InvalidArgumentException $e) {
            $errors = json_decode($e->getMessage(), true);
            JsonResponse::createStandard($errors, 400, 'error')->send();
        }
    }

    /**
     * GET /api/stores/{id}
     */
    public function show(int $id): void
    {
        $store = $this->storeService->getStoreById($id);

        if (!$store) {
            JsonResponse::createStandard(['message' => 'Magasin introuvable.'], 404, 'error')->send();
            return;
        }

        // Format output structure using StoreResponse
        $response = new StoreResponse($store);
        JsonResponse::createStandard($response->toArray(), 200)->send();
    }

    /**
     * PUT /api/stores/{id}
     */
    public function update(int $id): void
    {
        $store = $this->storeService->getStoreById($id);

        if (!$store) {
            JsonResponse::createStandard(['message' => 'Magasin introuvable.'], 404, 'error')->send();
            return;
        }

        $content = json_decode($this->request->getContent(), true) ?? [];

        try {
            $dto = new StoreUpdateInput($content, $store);
            $updatedStore = $this->storeService->updateStore($dto, $store);

            // Format output structure using StoreResponse
            $response = new StoreResponse($updatedStore);
            JsonResponse::createStandard($response->toArray(), 200)->send();
        } catch (\InvalidArgumentException $e) {
            $errors = json_decode($e->getMessage(), true);
            JsonResponse::createStandard($errors, 400, 'error')->send();
        }
    }

    /**
     * DELETE /api/stores/{id}
     */
    public function delete(int $id): void
    {
        $store = $this->storeService->getStoreById($id);

        if (!$store) {
            JsonResponse::createStandard(['message' => 'Magasin introuvable.'], 404, 'error')->send();
            return;
        }

        $this->storeService->deleteStore($id);
        JsonResponse::createStandard(['message' => 'Magasin supprimé avec succès.'], 200)->send();
    }
}
