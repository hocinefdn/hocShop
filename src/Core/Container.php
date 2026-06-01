<?php

namespace App\Core;

class Container
{
    private array $services = [];
    private array $instances = [];

    public function set(string $id, callable $callable): void
    {
        $this->services[$id] = $callable;
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) return $this->instances[$id];
        if (!isset($this->services[$id])) {
            throw new \RuntimeException("Service {$id} non trouvé.");
        }
        return $this->instances[$id] = $this->services[$id]($this);
    }
}
