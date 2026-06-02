<?php

namespace App\Store;

use App\Store\Store;
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
     * Find all stores with dynamic filtering and sorting
     * @param array $filters Query parameters for filtering (e.g., ['city' => 'Paris', 'is_active' => true])
     * @param string $sort Field to sort by
     * @param string $direction Sort direction ('ASC' or 'DESC')
     * @return array
     */
    public function findAll(array $filters = [], string $sort = 'id', string $direction = 'ASC'): array
    {
        // 1. Base query
        $sql = "SELECT * FROM stores WHERE 1=1";
        $parameters = [];

        // 2. Dynamic Filtering
        if (!empty($filters['city'])) {
            $sql .= " AND city = :city";
            $parameters['city'] = $filters['city'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND is_active = :is_active";
            $parameters['is_active'] = (int)$filters['is_active'];
        }

        if (!empty($filters['postal_code'])) {
            $sql .= " AND postal_code = :postal_code";
            $parameters['postal_code'] = $filters['postal_code'];
        }

        // 3. Whitelisting Sort Fields to prevent SQL Injection
        $allowedSortFields = ['id', 'name', 'city', 'postal_code', 'created_at'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // 4. Whitelisting Sort Direction
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        // 5. Append Order By clause safely using whitelisted values
        $sql .= " ORDER BY {$sort} {$direction}";

        // 6. Execute prepared statement
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parameters);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
