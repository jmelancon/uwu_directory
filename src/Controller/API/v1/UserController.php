<?php
declare(strict_types=1);

namespace App\Controller\API\v1;

use App\Entity\User;
use App\Security\LdapUserProvider;
use App\Service\CRUD\CreateEntity\UserCreator;
use App\Service\CRUD\DeleteEntity\UserDeleter;
use App\Service\CRUD\ReadEntity\ReadUserGroups;
use App\Service\CRUD\UpdateEntity\UserGroupModifier;
use App\Service\CRUD\UpdateEntity\UserUpdater;
use App\Service\Voter\GroupVoter;
use App\Service\Voter\UserVoter;
use App\Struct\Response\HandledResponse;
use App\Struct\Response\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
        private readonly UserCreator         $userCreator,
        private readonly UserDeleter         $userDeleter,
        private readonly UserUpdater         $userUpdater,
        private readonly UserGroupModifier   $userGroupModifier,
        private readonly LdapUserProvider    $ldapUserProvider,
        private readonly SerializerInterface $serializer, private readonly ReadUserGroups $readUserGroups,
    ){}

    #[Route(
        path: "/",
        name: ".create",
        methods: "POST",
        format: "json"
    )]
    #[IsGranted(
        attribute: UserVoter::NOT_USER_EXISTS,
        subject: new Expression("request.request.get('identifier')"),
        message: "A user with this username already exists.",
        statusCode: Response::HTTP_CONFLICT
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
        format: "json"
    )]
    #[IsGranted(
        attribute: UserVoter::USER_EXISTS,
        subject: new Expression("args['user']"),
        message: "The requested user does not exist.",
        statusCode: Response::HTTP_NOT_FOUND
    )]
    #[IsGranted(
        attribute: UserVoter::USER_NOT_CRITICAL,
        subject: new Expression("args['user']"),
        message: "The requested user is a critical user. They may not be deleted.",
        statusCode: Response::HTTP_BAD_REQUEST
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
        format: "json"
    )]
    #[IsGranted(
        attribute: UserVoter::USER_EXISTS,
        subject: new Expression("args['user']"),
        message: "The requested user does not exist.",
        statusCode: Response::HTTP_NOT_FOUND
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
        format: "json"
    )]
    #[IsGranted(
        attribute: UserVoter::USER_EXISTS,
        subject: new Expression("args['user']"),
        message: "The requested user does not exist.",
        statusCode: Response::HTTP_NOT_FOUND
    )]
    public function updateUser(
        string $user,
        #[MapRequestPayload] User $updatedUser
    ): JsonResponse
    {
        $this->userUpdater->update($user, $updatedUser);
        $this->userGroupModifier->batch($updatedUser->getIdentifier(), $updatedUser->getRoleDNs());
        return new JsonResponse(
            new RedirectResponse(
                title: "User Updated!",
                message: "The user has been updated. Your page will now reload to reflect these changes.",
                url: $this->generateUrl("administratorIndex")
            )
        );
    }

    #[Route(
        path: "/{user}/membership/{group}",
        name: "membership.delete",
        methods: "DELETE",
        format: "json"
    )]
    #[IsGranted(
        attribute: UserVoter::USER_EXISTS,
        subject: new Expression("args['user']"),
        message: "The requested user does not exist.",
        statusCode: Response::HTTP_NOT_FOUND
    )]
    #[IsGranted(
        attribute: GroupVoter::GROUP_EXISTS,
        subject: new Expression("args['group']"),
        message: "The requested group does not exist.",
        statusCode: Response::HTTP_NOT_FOUND
    )]
    #[IsGranted(
        attribute: new Expression("subject !== 'Basic Users'"),
        subject: new Expression("args['group']"),
        message: "Users may not be removed from the 'Basic Users' group.",
        statusCode: Response::HTTP_BAD_REQUEST
    )]
    public function deleteUserMembership(
        string $user,
        string $group
    ): JsonResponse
    {
        // Make sure the user actually has the group
        if (!$this->readUserGroups->has($user, $group))
            return new JsonResponse(
                new HandledResponse(
                    title: "Error 409",
                    message: "The user is not a member of the requested group.",
                )
            );

        $this->userGroupModifier->delete($user, $group);

        return new JsonResponse(
            new HandledResponse(
                title: "Modification Processed",
                message: "The user has been updated.",
            )
        );
    }
}