<?php
declare(strict_types=1);

namespace App\Security;

use App\Exception\IncompleteCredentialsException;
use App\Exception\InvalidCredentialsException;
use App\Exception\UserDoesNotExistException;
use App\Service\Condition\Exists\UserExistsCondition;
use App\Service\Ldap\LdapBindAuthentication;
use App\Struct\Response\HandledResponse;
use App\Struct\Response\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class LdapAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly LdapBindAuthentication $passwordChecker,
        private readonly UserExistsCondition    $userExists,
        private readonly UrlGeneratorInterface  $urlGenerator
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === "loginSubmit";
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request): Passport
    {
        // Grab credentials
        $username = $request->request->all()["username"] ?? null;
        $password = $request->request->all()["password"] ?? null;

        if (!$username || !$password){
            throw new IncompleteCredentialsException();
        }

        // Check if the user exists
        if (!$this->userExists->check($username)){
            throw new UserDoesNotExistException();
        }

        // Try password
        if (!$this->passwordChecker->auth($username, $password)){
            throw new InvalidCredentialsException();
        }

        return new SelfValidatingPassport(
            new UserBadge(
                userIdentifier: $username
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new JsonResponse(
            new RedirectResponse(
                "Success!",
                "You have successfully authenticated. You will now be redirected to the home page.",
                $this->urlGenerator->generate("root")
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            new HandledResponse(
                title: "Uh oh!",
                message: "Your credentials were incorrect. Please check your input and try again."
            )
        );
    }
}