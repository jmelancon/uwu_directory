<?php
declare(strict_types=1);

namespace App\Entity\Form;

use App\Entity\User;

class RegistrationAuthorization
{
    private RegistrationRequest $initialRequest;
    private array $grantedDns;

    public function getInitialRequest(): RegistrationRequest
    {
        return $this->initialRequest;
    }

    public function setInitialRequest(RegistrationRequest $initialRequest): void
    {
        $this->initialRequest = $initialRequest;
    }

    public function getGrantedDns(): array
    {
        return $this->grantedDns;
    }

    public function setGrantedDns(array $grantedDns): void
    {
        $this->grantedDns = $grantedDns;
    }

    public function asUser(): User
    {
        return new User(
            identifier: $this->getInitialRequest()->getIdentifier(),
            firstName:  $this->getInitialRequest()->getFirstName(),
            lastName: $this->getInitialRequest()->getLastName(),
            email: $this->getInitialRequest()->getIdentifier() . "@example.com"
        );
    }
}