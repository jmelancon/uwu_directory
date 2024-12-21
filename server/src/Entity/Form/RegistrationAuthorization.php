<?php

namespace App\Entity\Form;

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
}