<?php

namespace App\Controller;

use App\Core\Container;
use App\Core\JsonResponse;
use App\Entity\Store;
use App\Repository\StoreRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StoreController
{
    private StoreRepository $storeRepository;
    private Request $request;
    private ValidatorInterface $validator;

    public function __construct(Container $container)
    {
        $this->storeRepository = new StoreRepository($container->get(\PDO::class));
        $this->request = $container->get(Request::class);
        $this->validator = $container->get(ValidatorInterface::class);
    }

    /**
     * GET /api/stores
     * Liste tous les magasins
     */
    public function index(): void
    {
        $stores = $this->storeRepository->findAll();

        $data = array_map(function (Store $store) {
            return $store->toArray();
        }, $stores);

        JsonResponse::createStandard($data, 200)->send();
    }

    /**
     * POST /api/stores
     */
    public function create(): void
    {
        $content = json_decode($this->request->getContent(), true) ?? [];

        $store = new Store(
            null,
            $content['name'] ?? '',
            $content['address'] ?? '',
            $content['postal_code'] ?? '',
            $content['city'] ?? '',
            $content['is_active'] ?? true
        );

        $violations = $this->validator->validate($store);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            JsonResponse::createStandard($errors, 400, 'error')->send();
            return;
        }

        $savedStore = $this->storeRepository->save($store);
        JsonResponse::createStandard($savedStore->toArray(), 201)->send();
    }

    /**
     * GET /api/stores/{id}
     */
    public function show(int $id): void
    {
        $store = $this->storeRepository->find($id);

        if (!$store) {
            JsonResponse::createStandard(['message' => 'Magasin introuvable.'], 404, 'error')->send();
            return;
        }

        JsonResponse::createStandard($store->toArray(), 200)->send();
    }

    /**
     * PUT /api/stores/{id}
     */
    public function update(int $id): void
    {
        $store = $this->storeRepository->find($id);

        if (!$store) {
            JsonResponse::createStandard(['message' => 'Magasin introuvable.'], 404, 'error')->send();
            return;
        }

        $content = json_decode($this->request->getContent(), true) ?? [];

        $store->name = $content['name'] ?? $store->name;
        $store->address = $content['address'] ?? $store->address;
        $store->postalCode = $content['postal_code'] ?? $store->postalCode;
        $store->city = $content['city'] ?? $store->city;
        $store->isActive = $content['is_active'] ?? $store->isActive;

        $violations = $this->validator->validate($store);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            JsonResponse::createStandard($errors, 400, 'error')->send();
            return;
        }

        $this->storeRepository->update($store);
        JsonResponse::createStandard($store->toArray(), 200)->send();
    }

    /**
     * DELETE /api/stores/{id}
     */
    public function delete(int $id): void
    {
        $store = $this->storeRepository->find($id);

        if (!$store) {
            JsonResponse::createStandard(['message' => 'Magasin introuvable.'], 404, 'error')->send();
            return;
        }

        $this->storeRepository->delete($id);
        JsonResponse::createStandard(['message' => 'Magasin supprimé avec succès.'], 200)->send();
    }
}
