<?php
declare(strict_types=1);

namespace App\Service\CRUD\ReadEntity;

use App\Service\Ldap\LdapAggregator;

readonly class ReadUserGroups
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string         $userDn,
        private string         $groupDn,
    ){}

    public function fetch(string $username): ?array{
        // Escape username
        $escUser = ldap_escape($username);

        // Pull user
        $query = $this->ldapAggregator->getSymfonyProvider()->query($this->userDn, "(cn=$escUser)");
        return $query->execute()?->toArray()[0]?->getAttribute("memberOf") ?? null;
    }

    public function has(string $username, string $group): bool{
        // Get groups
        $has = $this->fetch($username);
        return in_array("CN=" . ldap_escape($group, flags: LDAP_ESCAPE_FILTER) . "," . $this->groupDn, $has);
    }
}