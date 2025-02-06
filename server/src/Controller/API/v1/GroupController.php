<?php
declare(strict_types=1);

namespace App\Controller\API\v1;

use App\Entity\Group;
use App\Service\CRUD\CreateEntity\GroupCreator;
use App\Service\CRUD\DeleteEntity\GroupDeleter;
use App\Service\ValueResolver\LdapGroupListResolver;
use App\Service\Voter\GroupVoter;
use App\Struct\Response\HandledResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: "/api/v1/group",
    name: "api.v1.group",
)]
#[IsGranted("ROLE_SSO_ADMINISTRATORS")]
class GroupController extends AbstractController
{
    public function __construct(
        private readonly GroupCreator $groupCreator,
        private readonly GroupDeleter $groupDeleter
    ){}

    #[Route(
        path: "/create",
        name: ".create",
        methods: "POST",
        format: "json"
    )]
    #[IsGranted(
        attribute: GroupVoter::NOT_GROUP_EXISTS,
        subject: new Expression("request.request.get('name')"),
        message: "A group with this name already exists.",
        statusCode: Response::HTTP_CONFLICT
    )]
    public function createGroup(
        #[MapRequestPayload] Group $group
    ): JsonResponse
    {
        $this->groupCreator->create($group->getName());
        return new JsonResponse(
            new HandledResponse(
                title: "Success!",
                message: "Your group has been created."
            )
        );
    }

    #[Route(
        path: "/all",
        name: ".all",
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
        path: "/{group}",
        name: ".delete",
        methods: "DELETE",
        format: "json"
    )]
    #[IsGranted(
        attribute: GroupVoter::GROUP_EXISTS,
        subject: new Expression("args['group']"),
        message: "The requested group does not exist.",
        statusCode: Response::HTTP_GONE
    )]
    #[IsGranted(
        attribute: GroupVoter::GROUP_NOT_CRITICAL,
        subject: new Expression("args['group']"),
        message: "The requested group is a critical group. It may not be deleted.",
        statusCode: Response::HTTP_BAD_REQUEST
    )]
    public function deleteGroup(
        string $group
    ): JsonResponse
    {
        $this->groupDeleter->delete($group);
        return new JsonResponse(
            new HandledResponse(
                title: "Group Deleted.",
                message: "The group has been deleted."
            )
        );
    }
}