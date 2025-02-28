<?php
declare(strict_types=1);

namespace App\Controller\API\v1;

use App\Interface\PageableInterface;
use App\Service\DataTableSource\GroupDataTableProvider;
use App\Service\DataTableSource\MemberDataTableProvider;
use App\Service\DataTableSource\ServiceDataTableProvider;
use App\Service\DataTableSource\UserDataTableProvider;
use App\Struct\DataTables\TableRequest;
use App\Struct\DataTables\TableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: "/api/v1/table",
    name: "api.v1.table"
)]
#[IsGranted("ROLE_SSO_ADMINISTRATORS")]
class TableController extends AbstractController
{
    private function parse(TableRequest $request, PageableInterface $provider, array $context = []): JsonResponse{
        $rows = $provider->fetch(
            pageSize: $request->length,
            offset: $request->start,
            context: array_merge(["request" => $request], $context)
        );
        return new JsonResponse(
            new TableResponse(
                draw: $request->draw,
                recordsTotal: $rows->total,
                recordsFiltered: $rows->count,
                data: $rows->data
            )
        );
    }
    #[Route(
        path: "/user",
        name: ".user",
        methods: ["POST"]
    )]
    public function users(
        #[MapRequestPayload] TableRequest $request,
        UserDataTableProvider $provider
    ): JsonResponse
    {
        return $this->parse($request, $provider);
    }

    #[Route(
        path: "/service",
        name: ".service",
        methods: ["POST"]
    )]
    public function services(
        #[MapRequestPayload] TableRequest $request,
        ServiceDataTableProvider $provider
    ): JsonResponse
    {
        return $this->parse($request, $provider);
    }

    #[Route(
        path: "/group/{group}",
        name: ".group.member",
        methods: "POST"
    )]
    public function members(
        #[MapRequestPayload] TableRequest $request,
        MemberDataTableProvider $provider,
        string $group
    ): JsonResponse
    {
        return $this->parse($request, $provider, ["group" => $group]);
    }

    #[Route(
        path: "/group",
        name: ".group",
        methods: ["POST"]
    )]
    public function groups(
        #[MapRequestPayload] TableRequest $request,
        GroupDataTableProvider $provider
    ): JsonResponse
    {
        return $this->parse($request, $provider);
    }
}