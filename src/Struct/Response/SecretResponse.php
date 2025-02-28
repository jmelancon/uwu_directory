<?php
declare(strict_types=1);

namespace App\Struct\Response;

class SecretResponse extends HandledResponse
{
    public function __construct(
        string $title,
        string $message,
        public string $secret
    )
    {
        parent::__construct($title, $message);
        $this->responseType = "secret";
    }
}