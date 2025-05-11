<?php
declare(strict_types=1);

namespace App\Struct\Response;

class RedirectResponse extends HandledResponse
{
    public function __construct(
        string $title,
        string $message,
        public string $url,
        public bool $paramRedirectOK = false
    )
    {
        parent::__construct($title, $message);
        $this->responseType = "redirect";
    }
}