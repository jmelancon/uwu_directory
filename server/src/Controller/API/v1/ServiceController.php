<?php
declare(strict_types=1);

namespace App\Controller\API\v1;

use App\Entity\Service;
use App\Service\CRUD\CreateEntity\ServiceCreator;
use App\Service\CRUD\UpdateEntity\ServicePasswordGenerator;
use App\Struct\Response\SecretResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: "/api/v1/service",
    name: "api.v1.service"
)]
#[IsGranted("ROLE_SSO_ADMINISTRATORS")]
class ServiceController extends AbstractController
{
    public function __construct(
        private readonly ServiceCreator           $serviceCreator,
        private readonly ServicePasswordGenerator $servicePasswordGenerator,
    ){}

    #[Route(
        path: "/create",
        name: ".create",
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
}