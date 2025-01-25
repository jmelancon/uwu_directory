<?php
declare(strict_types=1);

namespace App\Struct\DataTables\Arguments;

class ColumnArgument
{
    /**
     * @param string $data
     * The column's data source.
     * @see https://datatables.net/reference/option/columns.data
     * 
     * @param string $name
     * The column's name. Not sure how this differs from <code>$data</code>!
     * @see https://datatables.net/reference/option/columns.name
     * 
     * @param bool $searchable
     * Flag to indicate if the column is searchable.
     * 
     * @param bool $orderable
     * Whether the column may be ordered.
     * 
     * @param SearchArgument $search
     * Search arguments specific to the column.
     */
    public function __construct(
        public string $data,
        public string $name,
        public bool $searchable,
        public bool $orderable,
        public SearchArgument $search
    ){}
}