<?php
declare(strict_types=1);

namespace App\Service\Condition\Exists;

use App\Service\Ldap\LdapAggregator;
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;


#[AsRoutingConditionService(alias: "serviceExists")]
readonly class ServiceExistsCondition
{

    public function __construct(
        private LdapAggregator $ldap,
        private string $serviceDn,
    ){}

    public function check(string $service): bool
    {
        // Escape group name to prevent malicious actors
        $escService = ldap_escape($service, flags: LDAP_ESCAPE_FILTER);

        // Check to see if a service with the chosen ID exists!
        $res = $this->ldap->getSymfonyProvider()->query(
            dn: $this->serviceDn,
            query: "(&(objectClass=user)(cn=$escService))"
        )->execute();

        return $res->count() == 1;
    }
}