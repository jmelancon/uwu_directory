<?php
declare(strict_types=1);

namespace App\Controller\API\v1;

use App\Entity\Group;
use App\Service\CRUD\CreateEntity\GroupCreator;
use App\Service\ValueResolver\LdapGroupListResolver;
use App\Struct\Response\HandledResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: "/api/v1/group",
    name: "api.v1.group"
)]
#[IsGranted("ROLE_SSO_ADMINISTRATORS")]
class GroupController extends AbstractController
{
    public function __construct(
        private readonly GroupCreator $groupCreator
    ){}

    #[Route(
        path: "/create",
        name: ".create",
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
}