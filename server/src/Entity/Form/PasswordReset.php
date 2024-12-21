<?php

namespace App\Entity\Form;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordReset
{
    #[Assert\NotBlank(
        message: "An identifier must be supplied in order to issue a reset email."
    )]
    private string $identifier;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }
}