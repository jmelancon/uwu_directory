<?php
declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener(
    event: "kernel.request",
    priority: 200
)]
class RequestListener
{
    public function onKernelRequest(RequestEvent $event): void{
        if ($event->getRequest()->headers->get('X-Requested-By') === 'uwu_client' && $event->getRequest()->getContent()) {
            // Map request content to parameters
            $content = $event->getRequest()->toArray();
            $event->getRequest()->request->add($content);
        }
    }
}