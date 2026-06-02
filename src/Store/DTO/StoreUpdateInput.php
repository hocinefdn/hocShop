<?php

namespace App\Store\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class StoreUpdateInput
{
    #[Assert\NotBlank(message: "The store name cannot be empty.")]
    #[Assert\Length(max: 255, maxMessage: "The name cannot exceed {{ limit }} characters.")]
    public string $name;

    #[Assert\NotBlank(message: "The address cannot be empty.")]
    public string $address;

    #[Assert\NotBlank(message: "The postal code cannot be empty.")]
    #[Assert\Regex(pattern: "/^[0-9]{5}$/", message: "The postal code must be exactly 5 digits.")]
    public string $postalCode;

    #[Assert\NotBlank(message: "The city cannot be empty.")]
    public string $city;

    public bool $isActive;

    public function __construct(array $data, \App\Store\Store $currentStore)
    {
        // Fallback to current entity values if the key is missing in the request payload
        $this->name = trim($data['name'] ?? $currentStore->name);
        $this->address = trim($data['address'] ?? $currentStore->address);
        $this->postalCode = trim($data['postal_code'] ?? $currentStore->postalCode);
        $this->city = trim($data['city'] ?? $currentStore->city);
        $this->isActive = isset($data['is_active']) ? (bool)$data['is_active'] : $currentStore->isActive;
    }
}
