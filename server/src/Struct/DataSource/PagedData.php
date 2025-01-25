<?php
declare(strict_types=1);

namespace App\Struct\DataSource;

/**
 * Vehicle for passing around paged data with metadata on the current page.
 */
class PagedData
{
    /**
     * @param int $count
     * The number of rows in this object.
     *
     * @param array<array>|array<void> $data
     * The data requested.
     *
     * @param int $total
     * The total number of rows in the data source.
     */
    public function __construct(
        public int $count,
        public int $total,
        public array $data
    ){}
}