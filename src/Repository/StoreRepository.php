<?php

namespace App\Repository;

use App\Entity\Store;
use PDO;

class StoreRepository
{
    public function __construct(private PDO $pdo) {}

    public function save(Store $store): Store
    {
        $sql = "INSERT INTO stores (name, address, postal_code, city, is_active) 
                VALUES (:name, :address, :postal_code, :city, :is_active)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $store->name,
            'address' => $store->address,
            'postal_code' => $store->postalCode,
            'city' => $store->city,
            'is_active' => (int)$store->isActive
        ]);

        $store->id = (int)$this->pdo->lastInsertId();
        return $store;
    }

    // Conserve tes anciennes méthodes findAll() et find() ici...
}
