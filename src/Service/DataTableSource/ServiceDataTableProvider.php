<?php
declare(strict_types=1);

namespace App\Service\DataTableSource;

use App\Service\Ldap\LdapAggregator;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class ServiceDataTableProvider extends LdapGenericDataTableProvider
{
    public function __construct(
        LdapAggregator $ldapAggregator,
        RequestStack $requestStack,
        string $serviceDn,
    ){
        parent::__construct($ldapAggregator, $requestStack, $serviceDn);
    }

    protected function transformFromLdapCallback(array $value): array{
        return [
            "dn" => $value["dn"],
            "cn" => $value["cn"][0],
        ];
    }

    protected function getLdapRequiredAttributes(): array{
        return [
            "dn",
            "cn"
        ];
    }

    protected function createFilter(string $search = "", array $context = []): string{
        if ($search){
            $searchCriteria = ldap_escape($search);
            $filterAddition = "(|(cn=*$searchCriteria*))";
        } else {
            $filterAddition = "";
        }
        return "(&(objectClass=user)$filterAddition)";
    }
}