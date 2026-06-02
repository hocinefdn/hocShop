<?php

namespace App\Store\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class StoreCreateInput
{
    #[Assert\NotBlank(message: "The store name is required.")]
    #[Assert\Length(max: 255, maxMessage: "The name cannot exceed {{ limit }} characters.")]
    public string $name;

    #[Assert\NotBlank(message: "The address is required.")]
    public string $address;

    #[Assert\NotBlank(message: "The postal code is required.")]
    #[Assert\Regex(pattern: "/^[0-9]{5}$/", message: "The postal code must be exactly 5 digits.")]
    public string $postalCode;

    #[Assert\NotBlank(message: "The city is required.")]
    public string $city;

    public bool $isActive;

    public function __construct(array $data)
    {
        $this->name = trim($data['name'] ?? '');
        $this->address = trim($data['address'] ?? '');
        $this->postalCode = trim($data['postal_code'] ?? '');
        $this->city = trim($data['city'] ?? '');
        $this->isActive = (bool)($data['is_active'] ?? true);
    }
}
