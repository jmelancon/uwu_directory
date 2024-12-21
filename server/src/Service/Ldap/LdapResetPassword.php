<?php

namespace App\Service\Ldap;

use App\Entity\Form\PasswordReset;

readonly class LdapResetPassword
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string $userDn,
        private string $usernameSuffix
    ){}

    private function adifyPassword(string $password): string{
        return iconv("UTF-8", "UTF-16LE", '"' . $password . '"');
    }

    public function reset(PasswordReset $authorization, string $password): void
    {
        // Pull out a few details to make access easier
        $username = $authorization->getIdentifier();

        // Set DN
        $calculatedDn = "CN=" . $username . "$this->usernameSuffix," . $this->userDn;

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