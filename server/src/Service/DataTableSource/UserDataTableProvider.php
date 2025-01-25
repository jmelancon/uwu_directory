<?php
declare(strict_types=1);

namespace App\Service\DataTableSource;

use App\Service\Ldap\LdapAggregator;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class UserDataTableProvider extends LdapGenericDataTableProvider
{
    public function __construct(
        LdapAggregator $ldapAggregator,
        RequestStack $requestStack,
        string $userDn,
        private string $baseGroup
    ){
        parent::__construct($ldapAggregator, $requestStack, $userDn);
    }

    protected function transformFromLdapCallback(array $value): array{
        return [
            "firstName" => $value["givenname"][0],
            "lastName" => $value["sn"][0],
            "username" => $value["cn"][0],
            "email" => $value["mail"][0]
        ];
    }

    protected function getLdapRequiredAttributes(): array{
        return [
            "givenName",
            "sn",
            "mail",
            "cn"
        ];
    }

    protected function createFilter(string $search = ""): string{
        if ($search){
            $searchCriteria = ldap_escape($search);
            $filterAddition = "(|(cn=*$searchCriteria*)(displayName=*$searchCriteria*)(mail=*$searchCriteria*))";
        } else {
            $filterAddition = "";
        }
        return "(&(objectClass=user)(memberOf=" . $this->baseGroup . ")$filterAddition)";
    }
}