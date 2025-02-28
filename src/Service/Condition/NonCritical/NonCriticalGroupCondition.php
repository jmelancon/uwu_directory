<?php
declare(strict_types=1);

namespace App\Service\Condition\NonCritical;

use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;

#[AsRoutingConditionService(alias: "groupNotCritical")]
class NonCriticalGroupCondition
{
    public function check(string $group): bool
    {
        return !in_array(ldap_escape(strtolower($group)), [
            ldap_escape(strtolower("SSO Administrators")),
            ldap_escape(strtolower("Basic Users"))
        ]);
    }
}