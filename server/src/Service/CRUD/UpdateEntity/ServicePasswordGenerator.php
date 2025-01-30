<?php
declare(strict_types=1);

namespace App\Service\CRUD\UpdateEntity;

use App\Service\Ldap\LdapAggregator;
use Random\RandomException;

readonly class ServicePasswordGenerator
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string $serviceDn,
    ){}

    private function adifyPassword(string $password): string{
        return iconv("UTF-8", "UTF-16LE", '"' . $password . '"');
    }

    /**
     * @throws RandomException
     */
    public function set(string $serviceName): string
    {
        // Pull out a few details to make access easier
        $escName = ldap_escape($serviceName);

        // Set DN
        $calculatedDn = "CN=$escName,$this->serviceDn";

        // Generate a password
        $password = base64_encode(random_bytes(32));

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

        return $password;
    }
}