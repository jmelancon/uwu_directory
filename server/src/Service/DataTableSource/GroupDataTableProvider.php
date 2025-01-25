<?php
declare(strict_types=1);

namespace App\Service\DataTableSource;

use App\Service\Ldap\LdapAggregator;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class GroupDataTableProvider extends LdapGenericDataTableProvider
{

    public function __construct(
        LdapAggregator $ldapAggregator,
        RequestStack $requestStack,
        string $groupDn,
    ){
        parent::__construct($ldapAggregator, $requestStack, $groupDn);
    }

    protected function transformFromLdapCallback(array $value): array{
        return [
            "name" => $value["cn"][0],
            "size" => $value["member"]["count"] ?? 0,
        ];
    }

    protected function getLdapRequiredAttributes(): array{
        return [
            "cn",
            "member"
        ];
    }

    protected function createFilter(string $search = ""): string{
        if ($search){
            $searchCriteria = ldap_escape($search);
            $filterAddition = "(|(cn=*$searchCriteria*))";
        } else {
            $filterAddition = "";
        }
        return "(&(objectClass=group)$filterAddition)";
    }
}