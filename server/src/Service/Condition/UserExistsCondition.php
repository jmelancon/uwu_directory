<?php
declare(strict_types=1);

namespace App\Service\Condition;

use App\Entity\Form\RegistrationRequest;
use App\Service\Ldap\LdapAggregator;
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;


#[AsRoutingConditionService(alias: self::class)]
class UserExistsCondition
{

    public function __construct(
        private LdapAggregator $ldap,
        private string $userDn,
        private string $emailSuffix
    ){}

    public function check(string $username): bool
    {
        // Escape username to prevent malicious actors
        $escUser = ldap_escape($username);

        // Check to see if a user with the chosen ID exists!
        $res = $this->ldap->getSymfonyProvider()->query(
            dn: $this->userDn,
            query: "(&(objectClass=user)(mail=$escUser$this->emailSuffix))"
        )->execute();

        return $res->count() > 0;
    }
}