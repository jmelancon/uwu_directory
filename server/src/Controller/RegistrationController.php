<?php

namespace App\Controller;

use App\Entity\Form\PasswordBundle;
use App\Entity\Form\RegistrationAuthorization;
use App\Entity\Form\RegistrationRequest;
use App\Entity\Response\HandledResponse;
use App\Service\Condition\UserExistsCondition;
use App\Service\Ldap\LdapUserCreator;
use App\Service\Mailer;
use App\Service\Tokenizer;
use App\Service\ValueResolver\DecodedObjectResolver;
use App\Service\ValueResolver\LdapGroupListResolver;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/registration")]
class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly Tokenizer $tk,
        private readonly UserExistsCondition $userExists
    ){}

    #[Route(
        path: "/submit",
        name: "submitRegistration",
        methods: ["POST"],
        format: "json"
    )]
    public function hello(
        #[MapRequestPayload] RegistrationRequest $registrationRequest,
    ): JsonResponse {
        // Make sure the username isn't taken already
        if ($this->userExists->check($registrationRequest->getIdentifier()))
            return new JsonResponse(
                new HandledResponse(
                    "Uh oh!",
                    "This username is already registered. Please reset your password if you have forgotten it."
                )
            );

        // We'll need to fire off mail to one of the admins with a link to the route.
        $this->mailer->dispatch(
            to: $this->mailer->administratorAddress,
            subject: "📂 New account application recv'd",
            template: "mail/accountRequested.html.twig",
            context: [
                "subject" => $registrationRequest->getIdentifier(),
                "token" => $this->tk->encode($registrationRequest)
            ]
        );

        return new JsonResponse(
            data: new HandledResponse(
                "Request Submitted!",
                "Your request has been submitted. A moderator will review your request shortly."
            ),
            status: Response::HTTP_CREATED
        );
    }

    /**
     * Registration page. Returns a template and that's it.
     * @return void
     */
    #[Route(
        path: "/",
        name: "register",
        methods: ["GET"]
    )]
    #[Template(
        template: "forms/registration/register.html.twig"
    )]
    public function register(){}

    #[Route(
        path: "/grant",
        name: "grant",
        methods: ["GET"]
    )]
    #[Template("forms/registration/grant.html.twig", ["registration" => "registration", "groups" => "groups"])]
    public function grant(
        #[ValueResolver(DecodedObjectResolver::class)] RegistrationRequest $registration,
        /** @var $groups array<Entry> */
        #[ValueResolver(LdapGroupListResolver::class)] array $groups
    )
    {
    }

    #[Route(
        path: "/grant/submit",
        name: "grantSubmit",
        methods: ["POST"]
    )]
    public function grantSubmit(
        #[ValueResolver(DecodedObjectResolver::class)] RegistrationRequest $registration,
        Request $request
    ){
        // Make sure the username isn't taken already
        if ($this->userExists->check($registration->getIdentifier()))
            return new JsonResponse(
                new HandledResponse(
                    "Uh oh!",
                    "This username is already registered. The request may be stale."
                )
            );

        $authorization = new RegistrationAuthorization();

        // Each entry in the request payload is prefixed with "group_".
        // That simply will not do!
        // Something fuckin weird might happen if bad info is submitted here,
        // so, uh, don't do that.
        $authorization->setGrantedDns(preg_replace(
            pattern: '/group_(\S+)/',
            replacement: '$1',
            subject: array_keys(
                $request->request->all()["groupGrants"]
            )
        ));

        // Add in the original request information
        $authorization->setInitialRequest($registration);

        // Encrypt data and shoot it off to the requester
        // We'll need to fire off mail to one of the admins with a link to the route.
        $this->mailer->dispatch(
            to: $registration->getIdentifier() . $this->mailer->emailSuffix,
            subject: "✅ Account approved!",
            template: "mail/newPassSetLink.html.twig",
            context: [
                "user" => $registration->getFirstName(),
                "token" => $this->tk->encode($authorization)
            ]
        );

        return new Response(null, Response::HTTP_CREATED);
    }


    #[Route(
        path: "/createAccount",
        name: "createAccount",
        methods: ["GET"]
    )]
    #[Template("forms/registration/password.html.twig")]
    public function createAccount(
        // this stupid piece of shit exists solely to confirm that the user holds a registration
        // authorization.
        #[ValueResolver(DecodedObjectResolver::class)] RegistrationAuthorization $authorization,
    ){}

    #[Route(
        path: "/createAccount/submit",
        name: "createAccountSubmit",
        methods: ["POST"]
    )]
    public function createAccountSubmit(
        #[ValueResolver(DecodedObjectResolver::class)] RegistrationAuthorization $authorization,
        #[MapRequestPayload] PasswordBundle $passwordBundle,
        LdapUserCreator $creator
    ){
        // Make sure the username isn't taken already
        if ($this->userExists->check($authorization->getInitialRequest()->getIdentifier()))
            return new JsonResponse(
                new HandledResponse(
                    "Uh oh!",
                    "This username is already registered. If you'd like to change your password, please use the password reset page."
                )
            );

        $creator->create($authorization, $passwordBundle->getPassword());
        return new JsonResponse(
            new HandledResponse(
                title: "Hooray!",
                message: "Your account has been created. You may now use it to authenticate against ACM resources."
            )
        );
    }
}