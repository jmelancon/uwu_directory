<?php
declare(strict_types=1);

namespace App\Trait;

use LDAP\Connection;

trait LdapPasswordReset
{
    private function setPassword(string $fqcn, string $password, Connection $provider): void
    {
        ldap_modify_batch(
            ldap: $provider,
            dn: $fqcn,
            modifications_info: [
                [
                    "attrib"  => "unicodePwd",
                    "modtype" => LDAP_MODIFY_BATCH_REMOVE_ALL,
                ],
                [
                    "attrib"  => "unicodePwd",
                    "modtype" => LDAP_MODIFY_BATCH_ADD,
                    "values"  => [
                        iconv("UTF-8", "UTF-16LE", '"' . $password . '"')
                    ],
                ]
            ]
        );
    }
}