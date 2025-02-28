<?php
declare(strict_types=1);

namespace App\Service\DataTableSource;

use App\Service\Ldap\LdapAggregator;
use LogicException;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class MemberDataTableProvider extends UserDataTableProvider
{
    public function __construct(
        LdapAggregator $ldapAggregator,
        RequestStack $requestStack,
        string $userDn,
        private string $groupDn,
        string $baseGroup
    )
    {
        parent::__construct($ldapAggregator, $requestStack, $userDn, $baseGroup);
    }

    public function supports(int $pageSize, array $context = []): bool
    {
        return array_key_exists("group", $context) && parent::supports($pageSize, $context);
    }

    protected function createFilter(string $search = "", array $context = []): string{
        if (!(array_key_exists("group", $context) && is_string($context['group'])))
            throw new LogicException("\$context doesn't have the required key 'group'!");

        $groupFilter = "(memberOf=CN=" . ldap_escape($context['group'], flags: LDAP_ESCAPE_FILTER) . ",$this->groupDn)";

        if ($search){
            $searchCriteria = ldap_escape($search);
            $filterAddition = "(|(cn=*$searchCriteria*)(displayName=*$searchCriteria*)(mail=*$searchCriteria*))";
        } else {
            $filterAddition = "";
        }

        return "(&(objectClass=user)(memberOf=" . $this->baseGroup . ")$groupFilter$filterAddition)";
    }
}