<?php
declare(strict_types=1);

namespace App\Service\CRUD\UpdateEntity;

use App\Entity\User;
use App\Service\Ldap\LdapAggregator;
use App\Trait\LdapPasswordReset;

readonly class UserPasswordSetter
{
    use LdapPasswordReset;
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string $userDn,
    ){}

    public function set(User $user, string $password): void
    {
        // Pull out a few details to make access easier
        $username = ldap_escape($user->getIdentifier(), flags: LDAP_ESCAPE_DN);

        // Set DN
        $calculatedDn = "CN=$username,$this->userDn";

        $this->setPassword(
            fqcn: $calculatedDn,
            password: $password,
            provider: $this->ldapAggregator->getStockProvider()
        );
    }
}