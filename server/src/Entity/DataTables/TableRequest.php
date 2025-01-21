<?php
declare(strict_types=1);

namespace App\Entity\DataTables;

use App\Entity\DataTables\Arguments\ColumnArgument;
use App\Entity\DataTables\Arguments\OrderArgument;
use App\Entity\DataTables\Arguments\SearchArgument;

class TableRequest
{
    public function __construct(
        /**
         * @var int $draw
         * Like the frame number in TCP. Used to uniquely identify requests from a client.
         */
        public int $draw,

        /**
         * @var int $start
         * Paging indicator. Index based.
         */
        public int $start,

        /**
         * @var int $length
         * The number of rows the table can show.
         */
        public int $length,

        /**
         * @var SearchArgument $search
         * Search arguments. Can be regex or plain string.
         */
        public SearchArgument $search,

        /**
         * @var array<int, OrderArgument>|null $order
         * Parameters for how to order returned data.
         */
        public ?array $order,

        /**
         * @var array<int, ColumnArgument> $columns
         * Description of the requested columns.
         */
        public array $columns
    ){}
}