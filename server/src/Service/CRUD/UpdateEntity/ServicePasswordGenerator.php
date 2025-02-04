<?php
declare(strict_types=1);

namespace App\Service\CRUD\UpdateEntity;

use App\Service\Ldap\LdapAggregator;
use App\Trait\LdapPasswordReset;
use Random\RandomException;

readonly class ServicePasswordGenerator
{
    use LdapPasswordReset;

    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string $serviceDn,
    ){}

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

        $this->setPassword(
            fqcn: $calculatedDn,
            password: $password,
            provider: $this->ldapAggregator->getStockProvider()
        );

        return $password;
    }
}