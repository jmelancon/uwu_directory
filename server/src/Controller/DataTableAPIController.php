<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DataTables\TableRequest;
use App\Entity\DataTables\TableResponse;
use App\Service\DataTableSource\LdapUserDataTableProvider;
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
        LdapUserDataTableProvider $provider
    ): JsonResponse
    {
        $rows = $provider->fetch(
            pageSize: $request->length,
            context: ["cookie" => $this->getUser()->getUserIdentifier()]
        );
        return new JsonResponse(
            new TableResponse(
                draw: $request->draw,
                recordsTotal: sizeof($rows),
                recordsFiltered: sizeof($rows),
                data: $rows
            )
        );
    }
}