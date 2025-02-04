<?php
declare(strict_types=1);

namespace App\Controller\API\v1;

use App\Entity\User;
use App\Security\LdapUserProvider;
use App\Service\CRUD\CreateEntity\UserCreator;
use App\Service\CRUD\DeleteEntity\UserDeleter;
use App\Service\CRUD\UpdateEntity\UserGroupModifier;
use App\Service\CRUD\UpdateEntity\UserUpdater;
use App\Struct\Response\HandledResponse;
use App\Struct\Response\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(
    path: "/api/v1/user",
    name: "api.v1.user"
)]
#[IsGranted("ROLE_SSO_ADMINISTRATORS")]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserCreator              $userCreator,
        private readonly UserDeleter              $userDeleter,
        private readonly UserUpdater              $userUpdater,
        private readonly UserGroupModifier        $userGroupModifier,
        private readonly LdapUserProvider         $ldapUserProvider,
        private readonly SerializerInterface      $serializer,
    ){}

    #[Route(
        path: "/",
        name: ".create",
        methods: "POST",
        condition: "not service('userExists').check(request.request.get('identifier'))",
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

    #[Route(
        path: "/{user}",
        name: ".delete",
        methods: "DELETE",
        condition: "service('userExists').check(params['user']) and service('userNotCritical').check(params['user'])",
        format: "json"
    )]
    public function deleteUser(
        string $user
    ): JsonResponse
    {
        $this->userDeleter->delete($user);
        return new JsonResponse(
            new HandledResponse(
                title: "User Deleted.",
                message: "The user has been deleted."
            )
        );
    }

    #[Route(
        path: "/{user}",
        name: ".read",
        methods: "GET",
        condition: "service('userExists').check(params['user'])",
        format: "json"
    )]
    public function getSingleUser(
        string $user
    ): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize(
                $this->ldapUserProvider->loadUserByIdentifier($user),
                format: "json"
            ),
            json: true
        );
    }

    #[Route(
        path: "/{user}",
        name: ".update",
        methods: "POST",
        condition: "service('userExists').check(params['user'])",
        format: "json"
    )]
    public function updateUser(
        string $user,
        #[MapRequestPayload] User $updatedUser
    ): JsonResponse
    {
        $this->userUpdater->update($user, $updatedUser);
        $this->userGroupModifier->write($updatedUser->getIdentifier(), $updatedUser->getRoleDNs());
        return new JsonResponse(
            new RedirectResponse(
                title: "User Updated!",
                message: "The user has been updated. Your page will now reload to reflect these changes.",
                url: $this->generateUrl("administratorIndex")
            )
        );
    }
}