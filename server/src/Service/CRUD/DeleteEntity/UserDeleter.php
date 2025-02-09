<?php
declare(strict_types=1);

namespace App\Service\CRUD\DeleteEntity;

use App\Service\Ldap\LdapAggregator;

readonly class UserDeleter
{
    public function __construct(
        private LdapAggregator     $ldapAggregator,
        private string             $userDn,
    ){}

    public function delete(string $username): void{
        $escUser = ldap_escape($username, flags: LDAP_ESCAPE_DN);
        ldap_delete(
            ldap: $this->ldapAggregator->getStockProvider(),
            dn: "CN=" . $escUser . "," . $this->userDn
        );
    }
}