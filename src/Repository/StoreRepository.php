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

    /**
     * Get list of stores with optional pagination
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM stores ORDER BY id DESC");
        $rows = $stmt->fetchAll();

        $stores = [];
        foreach ($rows as $row) {
            $stores[] = new Store(
                (int)$row['id'],
                $row['name'],
                $row['address'],
                $row['postal_code'],
                $row['city'],
                (bool)$row['is_active'],
                $row['created_at']
            );
        }

        return $stores;
    }

    /**
     * Trouver un magasin par son ID
     */
    public function find(int $id): ?Store
    {
        $stmt = $this->pdo->prepare("SELECT * FROM stores WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new Store(
            (int)$row['id'],
            $row['name'],
            $row['address'],
            $row['postal_code'],
            $row['city'],
            (bool)$row['is_active'],
            $row['created_at']
        );
    }


    /**
     * Mettre à jour un magasin
     */
    public function update(Store $store): bool
    {
        $sql = "UPDATE stores 
                SET name = :name, address = :address, postal_code = :postal_code, city = :city, is_active = :is_active 
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $store->id,
            'name' => $store->name,
            'address' => $store->address,
            'postal_code' => $store->postalCode,
            'city' => $store->city,
            'is_active' => (int)$store->isActive
        ]);
    }


    /**
     * Supprimer un magasin
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM stores WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
