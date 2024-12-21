<?php

namespace App\Service\Ldap;

class LdapGetUserGroups
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string $userDn,
        private string $emailSuffix
    ){}

    public function fetch(string $username){
        // Pull user
        $query = $this->ldapAggregator->getSymfonyProvider()->query($this->userDn, "(mail=$username$this->emailSuffix)");
        return $query->execute()?->toArray()[0]?->getAttribute("memberOf") ?? null;
    }
}