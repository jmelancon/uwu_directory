<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OpenIDSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE => [
                ['onAuthorizationRequestResolve',  0]
            ]
        ];
    }

    public function onAuthorizationRequestResolve(AuthorizationRequestResolveEvent $event): void{
        $event->resolveAuthorization(true);
    }
}