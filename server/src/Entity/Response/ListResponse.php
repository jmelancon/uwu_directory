<?php

namespace App\Entity\Response;

use App\Entity\Response\HandledResponse;

class ListResponse extends HandledResponse
{
    public function __construct(
        string $title,
        string $message,
        /** @var array<string> $listContents */
        public array $listContents
    )
    {
        parent::__construct($title, $message);
        $this->responseType = "list";
    }
}