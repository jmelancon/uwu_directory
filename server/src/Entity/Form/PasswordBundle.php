<?php

namespace App\Entity\Form;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordBundle
{
    #[Assert\NotBlank(
        message: "You must set a password."
    )]
    #[Assert\Length(
        min: 14,
        minMessage: "Your password must be at least 14 characters"
    )]
    #[Assert\NotCompromisedPassword(
        message: "Your password is reported as weak by haveibeenpwned.com's API and must be stronger."
    )]
    private string $password;

    #[Assert\Expression(
        expression: "this.getPassword() === this.getPasswordConfirm()",
        message: "Your passwords do not match."
    )]
    private string $passwordConfirm;

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPasswordConfirm(): string
    {
        return $this->passwordConfirm;
    }

    public function setPasswordConfirm(string $passwordConfirm): void
    {
        $this->passwordConfirm = $passwordConfirm;
    }
}