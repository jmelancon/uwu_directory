<?php
declare(strict_types=1);

namespace App\Entity\DataTables;

class TableResponse
{
    /**
     * @param int $draw
     * Like the frame number in TCP. Used to uniquely identify requests from a client.
     * Must match the one in the corresponding TableRequest.
     *
     * @param int $recordsTotal
     * The total number of records before user filters are applied.
     *
     * @param int $recordsFiltered
     * The number of records after filtering.
     *
     * @var array<array<string, int|float|string|bool>>|array<void> $data
     * The data that is to be displayed by the client.
     *
     * @var string|null $error
     * An error to be provided to the client. Do not provide one if all is well.
     */
    public function __construct(
        public int $draw,
        public int $recordsTotal,
        public int $recordsFiltered,
        public array $data,
        public ?string $error = null
    ){}
}