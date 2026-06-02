<?php

namespace App\Tests\Unit;

use App\Store\Store;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    /**
     * Teste que l'entité s'initialise correctement avec ses valeurs par défaut
     */
    public function testStoreEntityInitialization(): void
    {
        $store = new Store(
            null,
            'Magasin Test',
            '12 Rue Principale',
            '75000',
            'Paris'
        );

        $this->assertNull($store->id);
        $this->assertEquals('Magasin Test', $store->name);
        $this->assertTrue($store->isActive);
    }

    /**
     * Teste la méthode toArray() pour s'assurer du format de sortie (notamment le snake_case pour l'API)
     */
    public function testToArrayFormatting(): void
    {
        $store = new Store(
            42,
            'WShop Lyon',
            'Bellecour',
            '69002',
            'Lyon',
            false,
            '2026-06-02 12:00:00'
        );

        $array = $store->toArray();

        $this->assertEquals(42, $array['id']);
        $this->assertEquals('WShop Lyon', $array['name']);
        $this->assertArrayHasKey('postal_code', $array);
        $this->assertEquals('69002', $array['postal_code']);
        $this->assertFalse($array['is_active']);
    }


    /**
     * Teste le cycle de vie du statut d'un magasin
     */
    public function testToggleActiveStatus(): void
    {
        $store = new Store(null, 'Magasin', 'Adresse', '75000', 'Paris', true);

        $store->deactivate();
        $this->assertFalse($store->isActive);

        $store->activate();
        $this->assertTrue($store->isActive);
    }
}
