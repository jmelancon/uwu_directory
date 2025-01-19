<?php
declare(strict_types=1);

namespace App\Enum;

enum DataTableDirection: string
{
    case Ascending = "asc";
    case Descending = "desc";
}
