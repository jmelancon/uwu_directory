<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\DataTableSource\GroupDataTableProvider;
use App\Service\DataTableSource\UserDataTableProvider;
use App\Struct\DataTables\TableRequest;
use App\Struct\DataTables\TableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/api/v1/datatables")]
#[IsGranted("ROLE_SSO_ADMINISTRATORS")]
class DataTableAPIController extends AbstractController
{
    #[Route(
        path: "/users",
        name: "usersDataTable",
        methods: ["POST"]
    )]
    public function users(
        #[MapRequestPayload] TableRequest $request,
        UserDataTableProvider $provider
    ): JsonResponse
    {
        $rows = $provider->fetch(
            pageSize: $request->length,
            offset: $request->start,
            context: ["request" => $request]
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
        path: "/groups",
        name: "groupsDataTable",
        methods: ["POST"]
    )]
    public function groups(
        #[MapRequestPayload] TableRequest $request,
        GroupDataTableProvider $provider
    ): JsonResponse
    {
        $rows = $provider->fetch(
            pageSize: $request->length,
            offset: $request->start,
            context: ["request" => $request]
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
}