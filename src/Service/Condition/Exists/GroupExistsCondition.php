<?php
declare(strict_types=1);

namespace App\Service\Condition\Exists;

use App\Service\Ldap\LdapAggregator;
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;


#[AsRoutingConditionService(alias: "groupExists")]
readonly class GroupExistsCondition
{

    public function __construct(
        private LdapAggregator $ldap,
        private string $groupDn,
    ){}

    public function check(string $group): bool
    {
        // Escape group name to prevent malicious actors
        $escGroup = ldap_escape($group);

        // Check to see if a group with the chosen ID exists!
        $res = $this->ldap->getSymfonyProvider()->query(
            dn: $this->groupDn,
            query: "(&(objectClass=group)(cn=$escGroup))"
        )->execute();

        return $res->count() > 0;
    }
}