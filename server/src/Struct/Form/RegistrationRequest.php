<?php
declare(strict_types=1);

namespace App\Struct\Form;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationRequest
{
    #[Assert\NotBlank(
        message: "You must provide your NDUS identifier for verification purposes."
    )]
    private string $identifier;

    #[Assert\NotBlank(
        message: "You must provide a first name."
    )]
    private string $firstName;

    #[Assert\NotBlank(
        message: "You must provide a last name."
    )]
    private string $lastName;

    private ?string $additionalInfo;

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getAdditionalInfo(): ?string
    {
        return $this->additionalInfo;
    }

    public function setAdditionalInfo(?string $additionalInfo): void
    {
        $this->additionalInfo = $additionalInfo;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }
}