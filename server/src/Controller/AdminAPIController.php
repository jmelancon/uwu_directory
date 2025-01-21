<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Response\HandledResponse;
use App\Entity\User;
use App\Service\CreateEntity\UserCreator;
use App\Service\Ldap\LdapUserCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/api/v1/admin")]
#[IsGranted("ROLE_SSO_ADMINISTRATORS")]
class AdminAPIController extends AbstractController
{
    public function __construct(
        private readonly UserCreator $userCreator
    ){}
    #[Route(
        path: "/createUser",
        name: "adminAPICreateUser",
        methods: ["POST"],
        format: "json"
    )]
    public function createUser(
        #[MapRequestPayload] User $user
    ): JsonResponse
    {
        $this->userCreator->create($user);
        return new JsonResponse(
            new HandledResponse(
                title: "User Created!",
                message: "The user has been created."
            )
        );
    }
}