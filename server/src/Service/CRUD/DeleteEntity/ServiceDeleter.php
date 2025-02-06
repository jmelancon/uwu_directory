<?php
declare(strict_types=1);

namespace App\Service\CRUD\DeleteEntity;

use App\Service\Ldap\LdapAggregator;

readonly class ServiceDeleter
{
    public function __construct(
        private LdapAggregator     $ldapAggregator,
        private string             $serviceDn,
    ){}

    public function delete(string $service): void{
        $escService = ldap_escape($service);
        ldap_delete(
            ldap: $this->ldapAggregator->getStockProvider(),
            dn: "CN=" . $escService . "," . $this->serviceDn
        );
    }
}