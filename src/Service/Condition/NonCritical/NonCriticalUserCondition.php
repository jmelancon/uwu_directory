<?php
declare(strict_types=1);

namespace App\Service\Condition\NonCritical;

use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;

#[AsRoutingConditionService(alias: "userNotCritical")]
class NonCriticalUserCondition
{
    public function check(string $user): bool
    {
        return ldap_escape(strtolower($user)) !== ldap_escape(strtolower("Administrator"));
    }
}