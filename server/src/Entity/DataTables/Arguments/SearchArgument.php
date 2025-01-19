<?php
declare(strict_types=1);

namespace App\Entity\DataTables\Arguments;

class SearchArgument
{
    public function __construct(
        public string $value,
        public string $regex
    ){}
}