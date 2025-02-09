<?php
declare(strict_types=1);

namespace App\Service\CRUD\DeleteEntity;

use App\Service\Ldap\LdapAggregator;

readonly class GroupDeleter
{
    public function __construct(
        private LdapAggregator     $ldapAggregator,
        private string             $groupDn,
    ){}

    public function delete(string $group): void{
        $escGroup = ldap_escape($group, flags: LDAP_ESCAPE_DN);
        ldap_delete(
            ldap: $this->ldapAggregator->getStockProvider(),
            dn: "CN=" . $escGroup . "," . $this->groupDn
        );
    }
}