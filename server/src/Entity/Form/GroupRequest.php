<?php
declare(strict_types=1);

namespace App\Entity\Form;

use Symfony\Component\Validator\Constraints as Assert;

class GroupRequest
{
    #[Assert\NotBlank(
        message: "You must provide your identifier."
    )]
    private string $identifier;

    #[Assert\NotBlank(
        message: "We need to know what you want, fill in the request details!"
    )]
    private string $requestDetails;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getRequestDetails(): string
    {
        return $this->requestDetails;
    }

    public function setRequestDetails(string $requestDetails): void
    {
        $this->requestDetails = $requestDetails;
    }

}