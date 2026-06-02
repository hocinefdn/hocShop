<?php

namespace App\Store;

use App\Store\DTO\StoreCreateInput;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StoreService
{
    private StoreRepository $repository;
    private ValidatorInterface $validator;

    public function __construct(StoreRepository $repository, ValidatorInterface $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * Fetch all stores with filters and sorting rules
     * 
     * @param array<string, mixed> $filters
     * @param string $sort
     * @param string $direction
     * @return Store[]
     */

    public function getStoreList(array $filters = [], string $sort = 'id', string $direction = 'ASC'): array
    {
        return $this->repository->findAll($filters, $sort, $direction);
    }

    /**
     * Fetch a single store by its ID as an object
     */
    public function getStoreById(int $id): ?Store
    {
        return $this->repository->find($id);
    }

    /**
     * Handle store creation with strict validation
     * @throws \InvalidArgumentException If validation fails
     */

    public function createStore(StoreCreateInput $input): Store
    {
        // 1. Enforce validation on the input DTO data structure
        $this->validateDTO($input);

        // 2. Map clean DTO data to domain Entity
        $store = new Store(
            null,
            $input->name,
            $input->address,
            $input->postalCode,
            $input->city,
            $input->isActive
        );

        // 3. Persist via repository
        return $this->repository->save($store);
    }

    /**
     * Handle store update processing
     * @throws \InvalidArgumentException If validation fails
     */
    public function updateStore(\App\Store\DTO\StoreUpdateInput $input, Store $store): Store
    {
        // Transfer validated properties from DTO to the domain Entity
        $store->name = $input->name;
        $store->address = $input->address;
        $store->postalCode = $input->postalCode;
        $store->city = $input->city;
        $store->isActive = $input->isActive;

        // Validate the DTO constraints before updating database
        $errors = $this->validator->validate($input);
        if (count($errors) > 0) {
            $validationErrors = [];
            foreach ($errors as $error) {
                $validationErrors[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new \InvalidArgumentException(json_encode($validationErrors));
        }

        $this->repository->update($store);
        return $store;
    }

    /**
     * Handle store data deletion request
     */
    public function deleteStore(int $id): void
    {
        $this->repository->delete($id);
    }



    /**
     * Centralized DTO validator constraint checker
     */
    private function validateDTO(StoreCreateInput $input): void
    {
        $errors = $this->validator->validate($input);

        if (count($errors) > 0) {
            $validationErrors = [];
            foreach ($errors as $error) {
                $validationErrors[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new \InvalidArgumentException(json_encode($validationErrors));
        }
    }
}
