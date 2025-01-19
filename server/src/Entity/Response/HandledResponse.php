<?php
declare(strict_types=1);

namespace App\Entity\Response;

class HandledResponse
{
    public readonly string $sentinel;
    public string $responseType;

    public function __construct(
        public string $title,
        public string $message
    ){
        $this->sentinel = "omg haiiiiiii :3";
        $this->responseType = "vanilla";
    }
}