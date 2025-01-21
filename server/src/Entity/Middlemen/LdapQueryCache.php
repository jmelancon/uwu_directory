<?php
declare(strict_types=1);

namespace App\Entity\Middlemen;

/**
 * @property array $previousData,
 * @property int $lastDraw
 * @property string $genesisFilter
 */
class LdapQueryCache
{
    public function __construct(
        public array $cachedRows = [],
        public string $genesisFilter = "",
        public int $lastDraw = 0,
    ){}
}