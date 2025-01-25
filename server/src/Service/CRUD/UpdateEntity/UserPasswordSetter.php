<?php
declare(strict_types=1);

namespace App\Service\CRUD\UpdateEntity;

use App\Entity\User;
use App\Service\Ldap\LdapAggregator;

readonly class UserPasswordSetter
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string $userDn,
    ){}

    private function adifyPassword(string $password): string{
        return iconv("UTF-8", "UTF-16LE", '"' . $password . '"');
    }

    public function set(User $user, string $password): void
    {
        // Pull out a few details to make access easier
        $username = ldap_escape($user->getIdentifier());

        // Set DN
        $calculatedDn = "CN=$username,$this->userDn";

        // Set password
        ldap_modify_batch(
            ldap: $this->ldapAggregator->getStockProvider(),
            dn: $calculatedDn,
            modifications_info: [
                [
                    "attrib"  => "unicodePwd",
                    "modtype" => LDAP_MODIFY_BATCH_REMOVE_ALL,
                ],
                [
                    "attrib"  => "unicodePwd",
                    "modtype" => LDAP_MODIFY_BATCH_ADD,
                    "values"  => [$this->adifyPassword($password)],
                ]
            ]
        );
    }
}