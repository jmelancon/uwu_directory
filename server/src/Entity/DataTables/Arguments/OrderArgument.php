<?php
declare(strict_types=1);

namespace App\Entity\DataTables\Arguments;

use App\Enum\DataTableDirection;

class OrderArgument
{
    public function __construct(
        /**
         * @var int $column
         * Index reference to an OrderColumn.
         */
        public int $column,

        /**
         * @var DataTableDirection $dir
         * The direction in which the data should be sorted.
         */
        public DataTableDirection $dir,

        /**
         * @var string $name
         * Name of the column. References an OrderColumn.
         */
        public string $name,
    ){}
}