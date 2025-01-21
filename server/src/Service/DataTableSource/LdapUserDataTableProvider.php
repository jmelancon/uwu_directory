<?php
declare(strict_types=1);

namespace App\Service\DataTableSource;

use App\Entity\DataTables\TableRequest;
use App\Entity\Middlemen\LdapQueryCache;
use App\Entity\Middlemen\PagedData;
use App\Enum\DataTableDirection;
use App\Interface\PageableInterface;
use App\Service\Ldap\LdapAggregator;
use LDAP\Result;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class LdapUserDataTableProvider
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private RequestStack $requestStack,
        private string $userDn,
    ){}

    public function supports(int $pageSize, array $context = []): bool
    {
        return $pageSize <= 100
            && array_key_exists("request", $context)
            && ($context["request"] instanceof TableRequest);
    }

    protected function hasCache(): bool{
        return $this->requestStack->getSession()->get(self::class) instanceof LdapQueryCache;
    }

    protected function fetchCache(): ?LdapQueryCache{
        return $this->requestStack->getSession()->get(self::class);
    }

    protected function cacheNotExpired(int $draw, string $filter): bool{
        if (!$this->hasCache())
            return false;

        $cache = $this->fetchCache();

        return $draw > $cache->lastDraw && $cache->genesisFilter == $filter;
    }

    protected function setCache(LdapQueryCache $cache): void{
        $this->requestStack->getSession()->set(self::class, $cache);
    }

    protected function refreshCache(int $draw, string $filter): LdapQueryCache{
        // Search using given filter and cookie
        /** @var Result $res */
        $res = ldap_search(
            ldap: $this->ldapAggregator->getStockProvider(),
            base: $this->userDn,
            filter: $filter,
            attributes: [
                "givenName",
                "sn",
                "mail",
                "cn"
            ],
        );

        ldap_parse_result(
            ldap: $this->ldapAggregator->getStockProvider(),
            result: $res,
            error_code: $error_code,
            matched_dn: $matched_dn,
            error_message:  $error_message,
            referrals: $referrals,
            controls: $controls
        );

        // Get actual results
        $rows = ldap_get_entries(
            ldap: $this->ldapAggregator->getStockProvider(),
            result: $res
        );

        // Process data
        $data = array_map(
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

        $newCache = new LdapQueryCache(
            cachedRows: $data,
            genesisFilter: $filter,
            lastDraw: $draw
        );
        $this->setCache($newCache);

        return $newCache;
    }

    public function fetch(int $pageSize, int $offset = 0, array $context = []): PagedData
    {
        /** @var TableRequest $request */
        $request = $context["request"];

        // Check if we need to construct a filter
        if ($request->search->value){
            $searchCriteria = ldap_escape($request->search->value);
            $filterAddition = "(|(cn=*$searchCriteria*)(displayName=*$searchCriteria*)(mail=*$searchCriteria*))";
        } else {
            $filterAddition = "";
        }
        $filter = "(&(objectClass=user)(loginShell=/bin/bash)$filterAddition)";

        // Ensure there's available cache
        if (!$this->cacheNotExpired($request->draw, $filter))
            $cache = $this->refreshCache($request->draw, $filter);
        else
            $cache = $this->fetchCache();

        $cache->lastDraw = $request->draw;
        $this->setCache($cache);

        if ($request->order){
            $column = $request->columns[$request->order[0]->column]->data;
            $order = $request->order[0]->dir === DataTableDirection::Ascending ? SORT_ASC : SORT_DESC;
            array_multisort(
                array_map('strtolower', array_column($cache->cachedRows, $column)), $order, SORT_NATURAL,
                $cache->cachedRows
            );
        }

        return new PagedData(
            count: sizeof($cache->cachedRows),
            total: sizeof($cache->cachedRows),
            data: array_slice(
                array: $cache->cachedRows,
                offset: $offset,
                length: $pageSize
            )
        );
    }
}