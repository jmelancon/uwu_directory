<?php
declare(strict_types=1);

namespace App\Service\Condition;

use App\Service\Ldap\LdapAggregator;
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;


#[AsRoutingConditionService(alias: "userExists")]
readonly class UserExistsCondition
{

    public function __construct(
        private LdapAggregator $ldap,
        private string $userDn,
        private string $baseGroup
    ){}

    public function check(string $username): bool
    {
        // Escape username to prevent malicious actors
        $escUser = ldap_escape($username);

        // Check to see if a user with the chosen ID exists!
        $res = $this->ldap->getSymfonyProvider()->query(
            dn: $this->userDn,
            query: "(&(objectClass=user)(cn=$escUser)(memberOf=$this->baseGroup))"
        )->execute();

        return $res->count() > 0;
    }
}