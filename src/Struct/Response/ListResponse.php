<?php
declare(strict_types=1);

namespace App\Struct\Response;

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