<?php
declare(strict_types=1);

namespace App\Struct\Response;

/**
 * A user can cause various problems and break stuff. This entity
 * can be used as a vehicle
 */
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