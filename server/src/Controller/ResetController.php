<?php

namespace App\Controller;

use App\Entity\Form\PasswordBundle;
use App\Entity\Form\PasswordReset;
use App\Entity\Response\HandledResponse;
use App\Service\Condition\UserExistsCondition;
use App\Service\Ldap\LdapResetPassword;
use App\Service\Mailer;
use App\Service\Tokenizer;
use App\Service\ValueResolver\DecodedObjectResolver;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/reset")]
class ResetController extends AbstractController
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly Tokenizer $tk,
        private readonly UserExistsCondition $userExists
    ){}

    #[Route(
        path: "/",
        name: "resetForm",
        methods: ["GET"]
    )]
    #[Template("forms/reset/request.html.twig")]
    public function resetForm(){}

    #[Route(
        path: "/submit",
        name: "resetFormSubmit",
        methods: ["POST"],
        format: "json"
    )]
    public function resetFormSubmit(
        #[MapRequestPayload] PasswordReset $passwordReset
    ){
        try{
            if ($this->userExists->check($passwordReset->getIdentifier())){
                $resetToken = $this->tk->encode($passwordReset);

                $this->mailer->dispatch(
                    to: $passwordReset->getIdentifier() . $this->mailer->emailSuffix,
                    subject: "🔒 Your ACM@UND Password Reset Request",
                    template: "mail/resetLink.html.twig",
                    context: [
                        "token" => $resetToken,
                        "name" => $passwordReset->getIdentifier()
                    ]
                );
            }
        } finally {
            return new JsonResponse(
                new HandledResponse(
                    "Reset requested!",
                    "If the account that you have requested a reset for exists, an email will be dispatched to the address shortly."
                )
            );
        }
    }

    #[Route(
        path: "/set",
        name: "setNewPassword",
        methods: ["GET"]
    )]
    #[Template("forms/reset/set.html.twig")]
    public function setNewPassword(
        #[ValueResolver(DecodedObjectResolver::class)] PasswordReset $authorization
    ){}

    #[Route(
        path: "/set/submit",
        name: "setNewPasswordSubmit",
        methods: ["POST"],
        format: "json"
    )]
    public function setNewPasswordSubmit(
        #[ValueResolver(DecodedObjectResolver::class)] PasswordReset $authorization,
        #[MapRequestPayload] PasswordBundle $passwordBundle,
        LdapResetPassword $resetter
    ){
        $resetter->reset($authorization, $passwordBundle->getPassword());

        return new JsonResponse(
            new HandledResponse(
                "Success!",
                "Your password has been reset."
            )
        );
    }
}