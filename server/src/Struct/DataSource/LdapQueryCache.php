<?php
declare(strict_types=1);

namespace App\Struct\DataSource;

/**
 * Vehicle for caching LDAP queries in session storage.
 */
class LdapQueryCache
{
    /**
     * @param array $cachedRows,
     * The row cache. This is a copy of the <b>entire</b> set of data. Probably will break things
     * on larger data sources, lol!
     *
     * @param int $lastDraw
     * The last <code>draw</code> property from the DataTables plugin. If this resets, that signals to
     * us that the user's refreshed the page.
     *
     * @param string $genesisFilter
     * The filter used to generate the data. If the filter changes, the cached data is invalid.
     */
    public function __construct(
        public array $cachedRows = [],
        public string $genesisFilter = "",
        public int $lastDraw = 0,
    ){}
}