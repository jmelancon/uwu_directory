<?php

namespace App\Entity\Form;

use Symfony\Component\Validator\Constraints as Assert;

class GroupResponse
{
    private array $grantedDns;

    #[Assert\NotBlank(
        message: "A verdict message must be provided for the user."
    )]
    private string $verdict;

    public function getGrantedDns(): array
    {
        return $this->grantedDns;
    }

    public function setGrantedDns(array $grantedDns): void
    {
        $this->grantedDns = $grantedDns;
    }

    public function getVerdict(): string
    {
        return $this->verdict;
    }

    public function setVerdict(string $verdict): void
    {
        $this->verdict = $verdict;
    }

}