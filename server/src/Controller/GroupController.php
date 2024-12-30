<?php

namespace App\Controller;

use App\Entity\Form\GroupRequest;
use App\Entity\Form\GroupResponse;
use App\Entity\Response\HandledResponse;
use App\Service\Condition\UserExistsCondition;
use App\Service\Ldap\LdapGetUserGroups;
use App\Service\Ldap\LdapGroupModifier;
use App\Service\Mailer;
use App\Service\Tokenizer;
use App\Service\ValueResolver\DecodedObjectResolver;
use App\Service\ValueResolver\LdapGroupListResolver;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/groups")]
class GroupController extends AbstractController
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly Tokenizer $tk,
        private readonly UserExistsCondition $userExists,
        private readonly LdapGetUserGroups $userGroups,
        private readonly LdapGroupModifier $modifier
    ){}

    #[Route(
        path: "/",
        name: "requestMoreGroups",
        methods: ["GET"]
    )]
    #[Template("forms/groups/request.html.twig")]
    public function requestMoreGroups(){}

    #[Route(
        path: "/submit",
        name: "requestMoreGroupsSubmit",
        methods: ["POST"]
    )]
    public function requestMoreGroupsSubmit(
        #[MapRequestPayload] GroupRequest $groupRequest
    ){
        try{
            if ($this->userExists->check($groupRequest->getIdentifier())) {
                $token = $this->tk->encode($groupRequest);

                $this->mailer->dispatch(
                    to: $this->mailer->administratorAddress,
                    subject: "📋 " . $groupRequest->getIdentifier() . " has requested new group permissions",
                    template: "mail/groupRequested.html.twig",
                    context: [
                        "token" => $token,
                        "subject" => $groupRequest->getIdentifier()
                    ]
                );
            }
        } finally {
            return new JsonResponse(
                new HandledResponse(
                    "Success!",
                    "Your request has been submitted. If the identifier provided is registered in the system, a moderator will review your request and message you upon reaching a verdict."
                )
            );
        }
    }

    #[Route(
        path: "/review",
        name: "reviewGroupRequest",
        methods: ["GET"]
    )]
    #[Template("forms/groups/review.html.twig")]
    public function reviewGroupRequest(
        #[ValueResolver(DecodedObjectResolver::class)] GroupRequest $groupRequest,
        #[ValueResolver(LdapGroupListResolver::class)] array $groups
    ){
        return [
            "groupRequest" => $groupRequest,
            "groups" => $groups,
            "existingGroups" => $this->userGroups->fetch($groupRequest->getIdentifier())
        ];
    }

    #[Route(
        path: "/review/submit",
        name: "reviewGroupRequestSubmit",
        methods: ["POST"]
    )]
    public function reviewGroupRequestSubmit(
        #[ValueResolver(DecodedObjectResolver::class)] GroupRequest $groupRequest,
        #[MapRequestPayload] GroupResponse $groupResponse,
    ){
        // Modify the groups according to admin response
        $this->modifier->write($groupRequest->getIdentifier(), $groupResponse->getGrantedDns());

        $this->mailer->dispatch(
            to: $groupRequest->getIdentifier() . $this->mailer->emailSuffix,
            subject: "📂 Your group request has been processed",
            template: "mail/groupsUpdated.html.twig",
            context: [
                "subject" => $groupRequest->getIdentifier(),
                "verdict" => $groupResponse->getVerdict()
            ]
        );

        return new JsonResponse(
            new HandledResponse(
                "Success!",
                "The user's groups have been updated. Your response has been emailed to the user."
            )
        );
    }
}