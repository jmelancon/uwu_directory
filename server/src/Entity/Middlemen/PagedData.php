<?php
declare(strict_types=1);

namespace App\Entity\Middlemen;

/**
 * @property int $count
 * The number of rows in this object.
 *
 * @property int $total
 * The total number of rows in the data source.
 *
 * @property array<array>|array<void> $data
 * The data requested.
 */
class PagedData
{
    public function __construct(
        public int $count,
        public int $total,
        public array $data
    ){}
}