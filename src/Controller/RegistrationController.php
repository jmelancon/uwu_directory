<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\InvalidUsernameException;
use App\Exception\PasswordRejectedException;
use App\Service\Condition\Exists\UserExistsCondition;
use App\Service\ConfigurationProvider;
use App\Service\CRUD\CreateEntity\UserCreator;
use App\Service\CRUD\DeleteEntity\UserDeleter;
use App\Service\CRUD\UpdateEntity\UserGroupModifier;
use App\Service\CRUD\UpdateEntity\UserPasswordSetter;
use App\Service\Mailer;
use App\Service\Tokenizer;
use App\Service\ValueResolver\DecodedObjectResolver;
use App\Service\ValueResolver\LdapGroupListResolver;
use App\Struct\Form\PasswordBundle;
use App\Struct\Form\RegistrationAuthorization;
use App\Struct\Form\RegistrationRequest;
use App\Struct\Response\HandledResponse;
use Exception;
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
        private readonly UserExistsCondition $userExists,
        private readonly ConfigurationProvider $config,
    ){}

    /**
     * @throws InvalidUsernameException
     */
    private function getEmailAddress(RegistrationRequest $registration): string
    {
        if ($registration->getEmail())
            return $registration->getEmail();

        $suffixedUsername = $registration->getIdentifier() . $this->config->getConfig()->getEmailSuffix();
        if (!filter_var($suffixedUsername, FILTER_VALIDATE_EMAIL))
            throw new InvalidUsernameException();

        return $suffixedUsername;
    }

    #[Route(
        path: "/submit",
        name: "submitRegistration",
        methods: ["POST"],
        format: "json"
    )]
    public function submitRegistration(
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
        template: "/views/forms/registration/register.html.twig"
    )]
    public function register(){}

    #[Route(
        path: "/grant",
        name: "grant",
        methods: ["GET"]
    )]
    #[Template("/views/forms/registration/grant.html.twig", ["registration" => "registration", "groups" => "groups"])]
    public function grant(
        #[ValueResolver(DecodedObjectResolver::class)] RegistrationRequest $registration,
        /** @var $groups array<Entry> */
        #[ValueResolver(LdapGroupListResolver::class)] array $groups
    ): void
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
    ): Response
    {
        // Make sure the username isn't taken already
        if ($this->userExists->check($registration->getIdentifier()))
            return new JsonResponse(
                new HandledResponse(
                    "Uh oh!",
                    "This username is already registered. The request may be stale."
                )
            );

        $authorization = new RegistrationAuthorization();

        // Just kinda throw it in there baus
        $authorization->setGrantedDns($request->request->all()["groupGrants"] ?? []);

        // Add in the original request information
        $authorization->setInitialRequest($registration);

        // Encrypt data and shoot it off to the requester
        // We'll need to fire off mail to one of the admins with a link to the route.
        $this->mailer->dispatch(
            to: $this->getEmailAddress($registration),
            subject: "✅ Account approved!",
            template: "mail/newPassSetLink.html.twig",
            context: [
                "user" => $registration->getFirstName(),
                "token" => $this->tk->encode($authorization)
            ]
        );

        return new JsonResponse(
            new HandledResponse(
                title: "Request Processed",
                message: "The user has been approved and notified."
            ),
        );
    }


    #[Route(
        path: "/createAccount",
        name: "createAccount",
        methods: ["GET"]
    )]
    #[Template("/views/forms/registration/password.html.twig")]
    public function createAccount(
        // this stupid piece of shit exists solely to confirm that the user holds a registration
        // authorization.
        #[ValueResolver(DecodedObjectResolver::class)] RegistrationAuthorization $authorization,
    ): void
    {}

    #[Route(
        path: "/createAccount/submit",
        name: "createAccountSubmit",
        methods: ["POST"]
    )]
    public function createAccountSubmit(
        #[ValueResolver(DecodedObjectResolver::class)] RegistrationAuthorization $authorization,
        #[MapRequestPayload] PasswordBundle $passwordBundle,
        UserCreator $creator,
        UserPasswordSetter $userPasswordSetter,
        UserDeleter $deleter,
        UserGroupModifier $groupModifier
    ): JsonResponse
    {
        // Make sure the username isn't taken already
        if ($this->userExists->check($authorization->getInitialRequest()->getIdentifier()))
            return new JsonResponse(
                new HandledResponse(
                    "Uh oh!",
                    "This username is already registered. If you'd like to change your password, please use the password reset page."
                )
            );

        // Parse out authorization to a user object
        $user = $authorization->asUser($this->getEmailAddress($authorization->getInitialRequest()));

        // Create the user
        $creator->create($user);

        // Set the password
        try{
            $userPasswordSetter->set($user, $passwordBundle->getPassword());
        } catch (Exception){
            $deleter->delete($user->getUserIdentifier());
            throw new PasswordRejectedException();
        }

        // Grant groups
        $groupModifier->batch($user->getUserIdentifier(), $authorization->getGrantedDns());

        $org = $this->config->getConfig()->getOrganization() ?? 'your organization\'s';

        return new JsonResponse(
            new HandledResponse(
                title: "Hooray!",
                message: "Your account has been created. You may now use it to authenticate against $org resources."
            )
        );
    }
}