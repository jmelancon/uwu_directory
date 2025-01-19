<?php
declare(strict_types=1);

namespace App\Entity\DataTables\Arguments;

class ColumnArgument
{
    public function __construct(
        /**
         * @var string $data
         * The column's data source.
         * @see https://datatables.net/reference/option/columns.data
         */
        public string $data,

        /**
         * @var string $name
         * The column's name. Not sure how this differs from $data!
         * @see https://datatables.net/reference/option/columns.name
         */
        public string $name,

        /**
         * @var bool $searchable
         * Flag to indicate if the column is searchable.
         */
        public bool $searchable,

        /**
         * @var bool $orderable
         * Whether the column may be ordered.
         */
        public bool $orderable,

        /**
         * @var SearchArgument $search
         * Search arguments specific to the column.
         */
        public SearchArgument $search
    ){}
}