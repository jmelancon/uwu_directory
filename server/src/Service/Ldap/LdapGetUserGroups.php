<?php
declare(strict_types=1);

namespace App\Service\Ldap;

class LdapGetUserGroups
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string $userDn,
        private string $emailSuffix
    ){}

    public function fetch(string $username): ?array{
        // Escape username
        $escUser = ldap_escape($username);
        // Pull user
        $query = $this->ldapAggregator->getSymfonyProvider()->query($this->userDn, "(mail=$escUser$this->emailSuffix)");
        return $query->execute()?->toArray()[0]?->getAttribute("memberOf") ?? null;
    }
}