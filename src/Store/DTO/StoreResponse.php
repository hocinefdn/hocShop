<?php

namespace App\Store\DTO;

use App\Store\Store;

class StoreResponse
{
    public int $id;
    public string $name;
    public string $address;
    public string $postalCode;
    public string $city;
    public bool $isActive;
    public string $createdAt;

    public function __construct(Store $store)
    {
        $this->id = (int)$store->id; // Enforce strict type casting
        $this->name = $store->name;
        $this->address = $store->address;
        $this->postalCode = $store->postalCode;
        $this->city = $store->city;
        $this->isActive = (bool)$store->isActive;
        $this->createdAt = $store->createdAt ?? date('Y-m-d H:i:s');
    }

    /**
     * Convert the output DTO properties into a standard array format for JSON responses
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
        ];
    }

    /**
     * Helper method to map a collection of Store entities into an array of response DTO arrays
     * @param Store[] $stores
     */
    public static function fromCollection(array $stores): array
    {
        return array_map(fn(Store $store) => (new self($store))->toArray(), $stores);
    }
}
