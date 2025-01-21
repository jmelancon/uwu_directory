<?php
declare(strict_types=1);

namespace App\Service\ReadEntity;

use App\Service\Ldap\LdapAggregator;

readonly class ReadUserGroups
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string         $userDn,
    ){}

    public function fetch(string $username): ?array{
        // Escape username
        $escUser = ldap_escape($username);

        // Pull user
        $query = $this->ldapAggregator->getSymfonyProvider()->query($this->userDn, "(cn=$escUser)");
        return $query->execute()?->toArray()[0]?->getAttribute("memberOf") ?? null;
    }
}