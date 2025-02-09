<?php
declare(strict_types=1);

namespace App\Service\CRUD\CreateEntity;

use App\Const\UserAccountControl;
use App\Service\Ldap\LdapAggregator;

readonly class ServiceCreator
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string $serviceDn,
        private string $baseDn,
    ){}

    protected function esc(string $str): string{
        return ldap_escape($str, flags: LDAP_ESCAPE_DN);
    }

    public function create(string $serviceName): void
    {
        // Escape service name
        $escName = $this->esc($serviceName);

        // Make the new DN
        $calculatedDn = "CN=" . $escName . "," . $this->serviceDn;

        // Create and persist new user
        ldap_add(
            ldap: $this->ldapAggregator->getStockProvider(),
            dn: $calculatedDn,
            entry: [
                "objectClass" => ["person", "organizationalPerson", "top", "user"],
                "objectCategory" => ["CN=Person,CN=Schema,CN=Configuration," . $this->baseDn],
                "cn" => [$escName],
                "userAccountControl" => [UserAccountControl::NORMAL_ACCOUNT | UserAccountControl::PASSWD_CANT_CHANGE],
            ]
        );
    }
}