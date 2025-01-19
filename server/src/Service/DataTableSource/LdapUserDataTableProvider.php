<?php
declare(strict_types=1);

namespace App\Service\DataTableSource;

use App\Interface\PageableInterface;
use App\Service\Ldap\LdapAggregator;
use LDAP\Result;

readonly class LdapUserDataTableProvider implements PageableInterface
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string $userDn,
    ){}

    public function supports(int $pageSize, array $context = []): bool
    {
        return $pageSize <= 100 && array_key_exists(LDAP_CONTROL_PAGEDRESULTS, $context);
    }

    public function fetch(int $pageSize, int $offset = 0, array $context = []): array
    {
        /** @var Result $res */
        $res = ldap_search(
            ldap: $this->ldapAggregator->getStockProvider(),
            base: $this->userDn,
            filter: "(&(objectClass=user)(loginShell=/bin/bash))",
            attributes: [
                "givenName",
                "sn",
                "mail",
                "cn"
            ],
            deref: LDAP_DEREF_NEVER,
            controls: [
                [
                    "oid" => LDAP_CONTROL_PAGEDRESULTS,
                    "value" => [
                        "cookie" => $context["cookie"],
                        "size" => $pageSize
                    ]
                ]
            ]
        );

        $rows = ldap_get_entries(
            ldap: $this->ldapAggregator->getStockProvider(),
            result: $res
        );

        if ($rows["count"] == 0)
            return [];

        return array_map(
            callback: function(array $value){
                return [
                    "firstName" => $value["givenname"][0],
                    "lastName" => $value["sn"][0],
                    "username" => $value["cn"][0],
                    "email" => $value["mail"][0]
                ];
            },
            array: array_slice(
                array: $rows,
                offset: 1
            )
        );
    }
}