<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Service;
use App\Entity\User;
use App\Security\LdapUserProvider;
use App\Service\CRUD\CreateEntity\GroupCreator;
use App\Service\CRUD\CreateEntity\ServiceCreator;
use App\Service\CRUD\CreateEntity\UserCreator;
use App\Service\CRUD\DeleteEntity\UserDeleter;
use App\Service\CRUD\UpdateEntity\ServicePasswordGenerator;
use App\Service\CRUD\UpdateEntity\UserGroupModifier;
use App\Service\CRUD\UpdateEntity\UserUpdater;
use App\Service\ValueResolver\LdapGroupListResolver;
use App\Struct\Response\HandledResponse;
use App\Struct\Response\RedirectResponse;
use App\Struct\Response\SecretResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/v1/admin")]
#[IsGranted("ROLE_SSO_ADMINISTRATORS")]
class AdminAPIController extends AbstractController
{
    public function __construct(
        private readonly UserCreator              $userCreator,
        private readonly UserDeleter              $userDeleter,
        private readonly UserUpdater              $userUpdater,
        private readonly UserGroupModifier        $userGroupModifier,
        private readonly LdapUserProvider         $ldapUserProvider,
        private readonly SerializerInterface      $serializer,
        private readonly ServiceCreator           $serviceCreator,
        private readonly ServicePasswordGenerator $servicePasswordGenerator,
        private readonly GroupCreator             $groupCreator
    ){}
    #[Route(
        path: "/user/create",
        name: "adminAPICreateUser",
        methods: "POST",
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
        path: "/service/create",
        name: "adminAPICreateService",
        methods: "POST",
        format: "json"
    )]
    public function createService(
        #[MapRequestPayload] Service $service
    ){
        $this->serviceCreator->create($service->getName());
        $secret = $this->servicePasswordGenerator->set($service->getName());
        return new JsonResponse(
            new SecretResponse(
                title: "Success!",
                message: "Your service has been created. Use the following password in your service's configuration:",
                secret: $secret
            )
        );
    }

    #[Route(
        path: "/group/create",
        name: "adminAPICreateGroup",
        methods: "POST",
        format: "json"
    )]
    public function createGroup(
        #[MapRequestPayload] Group $group
    ){
        $this->groupCreator->create($group->getName());
        return new JsonResponse(
            new HandledResponse(
                title: "Success!",
                message: "Your group has been created."
            )
        );
    }

    #[Route(
        path: "/user/{user}/delete",
        name: "adminAPIDeleteUser",
        methods: "POST",
        format: "json"
    )]
    public function deleteUser(
        User $user
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
        path: "/groups/all",
        name: "adminAPIAllGroups",
        methods: "GET",
        format: "json"
    )]
    public function allGroups(
        #[ValueResolver(LdapGroupListResolver::class)] array $groups
    ): JsonResponse
    {
        return new JsonResponse(
            array_map(
                function(Entry $group){
                    return [
                        "dn" => $group->getDn(),
                        "cn" => $group->getAttribute("cn")[0]
                    ];
                },
                $groups
            )
        );
    }

    #[Route(
        path: "/user/{user}",
        name: "adminAPIGetUser",
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
        path: "/user/{userIdentifier}/update",
        name: "adminAPIUpdateUser",
        methods: "POST",
        condition: "service('userExists').check(params['userIdentifier'])",
        format: "json"
    )]
    public function updateUser(
        string $userIdentifier,
        #[MapRequestPayload] User $updatedUser
    ): JsonResponse
    {
        $this->userUpdater->update($userIdentifier, $updatedUser);
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