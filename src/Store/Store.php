<?php

namespace App\Store;

use Symfony\Component\Validator\Constraints as Assert;

class Store
{
    public function __construct(
        public ?int $id = null,

        #[Assert\NotBlank(message: "Le nom du magasin est obligatoire.")]
        #[Assert\Length(max: 255, maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères.")]
        public string $name = '',

        #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
        public string $address = '',

        #[Assert\NotBlank(message: "Le code postal est obligatoire.")]
        #[Assert\Regex(pattern: "/^[0-9]{5}$/", message: "Le code postal doit contenir exactement 5 chiffres.")]
        public string $postalCode = '',

        #[Assert\NotBlank(message: "La ville est obligatoire.")]
        public string $city = '',

        public bool $isActive = true,
        public ?string $createdAt = null
    ) {}



    /**
     * Active le magasin
     */
    public function activate(): self
    {
        $this->isActive = true;
        return $this;
    }

    /**
     * Désactive le magasin
     */
    public function deactivate(): self
    {
        $this->isActive = false;
        return $this;
    }

    /**
     * @return array<string, mixed>
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
}
