<?php
declare(strict_types=1);

namespace App\Entity\Response;

class RedirectResponse extends HandledResponse
{
    public function __construct(
        string $title,
        string $message,
        public string $url
    )
    {
        parent::__construct($title, $message);
        $this->responseType = "redirect";
    }
}