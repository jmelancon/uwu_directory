<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Exception\PasswordRejectedException;
use App\Exception\TokenMissingException;
use App\Struct\Response\HandledResponse;
use App\Struct\Response\ListResponse;
use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Exception\RuleViolation;
use RangeException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(
    event: "kernel.exception"
)]
readonly class ExceptionListener
{
    public function __construct(
        private UrlGeneratorInterface $urlGen
    ){
    }
    public function __invoke(ExceptionEvent $event): void
    {
        // Grab some important exception context
        $exception = $event->getThrowable();
        $previous = $exception->getPrevious();
        $method = $event->getRequest()->getMethod();
        $isXmlHttp = $event->getRequest()->isXmlHttpRequest();

        // Other assorted things of use
        $flashes = $event->getRequest()->getSession()->getFlashBag();

        // CASE: User submits bad data in a form
        if ($previous instanceof ValidationFailedException && $method === Request::METHOD_POST){
            $event->setResponse(
                new JsonResponse(
                    new ListResponse(
                        title: "Input Error!",
                        message: "Your input was not valid. Please review the following items and try again:",
                        listContents: array_map(
                            function(ConstraintViolation $violation){
                                return $violation->getMessage();
                            },
                            iterator_to_array($previous->getViolations())
                        )
                    )
                )
            );
        }

        // CASE: User submits a password but is rejected by the LDAP server
        if ($exception instanceof PasswordRejectedException && $method === Request::METHOD_POST){
            $event->setResponse(
                new JsonResponse(
                    new HandledResponse(
                        title: "Uh oh!",
                        message: "The server rejected your password for an unknown reason. Please try a different password. "
                    )
                )
            );
        }

        // CASE: User requests page with expired token
        if ($exception instanceof RuleViolation && $method === Request::METHOD_GET){
            if ($exception->getMessage() === 'This token has expired.'){
                $flashes->add("error", "The form you have requested has expired.");
                goto redirectRoot;
            }
        }

        // CASE: User passes malformed token
        if (
            (
                $exception instanceof RangeException && ($exception->getMessage() === 'Incorrect padding')
                || $exception instanceof PasetoException
            ) && $method === Request::METHOD_GET)
        {
            $flashes->add("error", "The token associated with your request appears to be malformed.");
            goto redirectRoot;
        }

        // CASE: User requests non-existent page
        if ($exception instanceof NotFoundHttpException && !$isXmlHttp){
            $flashes->add("error", "The page you requested could not be found.");
            goto redirectRoot;
        }

        // CASE: User navigates to page that requires token, yet does not have one in query string
        if ($exception instanceof TokenMissingException && !$isXmlHttp){
            $flashes->add("error", "The token parameter was missing from your request. Please ensure that you have the complete link for the resource you are attempting to access.");
            goto redirectRoot;
        }

        return;

        redirectRoot:
        $event->setResponse(
            new RedirectResponse(
                $this->urlGen->generate("root")
            )
        );
    }
}