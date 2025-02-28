<?php
declare(strict_types=1);

namespace App\Service\DataTableSource;

use App\Enum\DataTableDirection;
use App\Interface\PageableInterface;
use App\Service\Ldap\LdapAggregator;
use App\Struct\DataTables\TableRequest;
use App\Struct\DataSource\LdapQueryCache;
use App\Struct\DataSource\PagedData;
use LDAP\Result;
use Symfony\Component\HttpFoundation\RequestStack;
use ValueError;

readonly abstract class LdapGenericDataTableProvider implements PageableInterface
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private RequestStack   $requestStack,
        private string         $baseDn,
    ){}

    public function supports(int $pageSize, array $context = []): bool
    {
        return $pageSize <= 100
            && array_key_exists("request", $context)
            && ($context["request"] instanceof TableRequest);
    }

    final protected function hasCache(): bool{
        return $this->requestStack->getSession()->get(self::class) instanceof LdapQueryCache;
    }

    final protected function fetchCache(): ?LdapQueryCache{
        return $this->requestStack->getSession()->get(self::class);
    }

    final protected function cacheNotExpired(int $draw, string $filter): bool{
        if (!$this->hasCache())
            return false;

        $cache = $this->fetchCache();

        return $draw > $cache->lastDraw && $cache->genesisFilter == $filter;
    }

    final protected function setCache(LdapQueryCache $cache): void{
        $this->requestStack->getSession()->set(self::class, $cache);
    }

    final protected function refreshCache(int $draw, string $filter): LdapQueryCache{
        // Search using given filter and cookie
        /** @var Result $res */
        $res = ldap_search(
            ldap: $this->ldapAggregator->getStockProvider(),
            base: $this->baseDn,
            filter: $filter,
            attributes: $this->getLdapRequiredAttributes(),
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
            callback: $this->transformFromLdapCallback(...),
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

    final public function fetch(int $pageSize, int $offset = 0, array $context = []): PagedData
    {
        if (!$this->supports($pageSize, $context))
            throw new ValueError("Context sucks bro...........");

        /** @var TableRequest $request */
        $request = $context["request"];

        // Get filter
        $filter = $this->createFilter($request->search ? $request->search->value : "", $context);

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

    protected abstract function getLdapRequiredAttributes(): array;
    protected abstract function transformFromLdapCallback(array $value): array;
    protected abstract function createFilter(string $search = "", array $context = []): string;
}