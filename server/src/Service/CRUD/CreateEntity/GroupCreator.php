<?php
declare(strict_types=1);

namespace App\Service\CRUD\CreateEntity;

use App\Service\Ldap\LdapAggregator;

readonly class GroupCreator
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string $groupDn,
        private string $baseDn,
    ){}

    protected function esc(string $str): string{
        return ldap_escape($str, flags: LDAP_ESCAPE_FILTER);
    }

    public function create(string $serviceName): void
    {
        // Escape service name
        $escName = $this->esc($serviceName);

        // Make the new DN
        $calculatedDn = "CN=" . $escName . "," . $this->groupDn;

        // Create and persist new user
        ldap_add(
            ldap: $this->ldapAggregator->getStockProvider(),
            dn: $calculatedDn,
            entry: [
                "objectClass" => ["group", "top"],
                "objectCategory" => ["CN=Group,CN=Schema,CN=Configuration," . $this->baseDn],
                "cn" => [$escName],
            ]
        );
    }
}