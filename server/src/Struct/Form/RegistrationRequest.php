<?php
declare(strict_types=1);

namespace App\Struct\Form;

use App\Validator as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationRequest
{
    #[Assert\NotBlank(
        message: "You must provide a username or organizational identifier."
    )]
    private string $identifier;

    #[CustomAssert\RegistrationRestricted]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

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