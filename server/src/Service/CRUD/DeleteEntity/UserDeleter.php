<?php
declare(strict_types=1);

namespace App\Service\CRUD\DeleteEntity;

use App\Entity\User;
use App\Service\Ldap\LdapAggregator;

readonly class UserDeleter
{
    public function __construct(
        private LdapAggregator     $ldapAggregator,
        private string             $userDn,
    ){}

    public function delete(User $user): void{
        $escUser = ldap_escape($user->getUserIdentifier());
        ldap_delete(
            ldap: $this->ldapAggregator->getStockProvider(),
            dn: $escUser . "," . $this->userDn
        );
    }
}