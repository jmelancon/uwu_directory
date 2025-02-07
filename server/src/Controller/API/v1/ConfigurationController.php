<?php
declare(strict_types=1);

namespace App\Controller\API\v1;

use App\Entity\Config;
use App\Service\ConfigurationProvider;
use App\Struct\Response\HandledResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: "/api/v1/config",
    name: "api.v1.config",
)]
#[IsGranted("ROLE_SSO_ADMINISTRATORS")]
class ConfigurationController extends AbstractController
{
    public function __construct(private readonly ConfigurationProvider $configurationProvider){}

    #[Route(
        path: "/",
        name: ".create",
        methods: "POST",
        format: "json"
    )]
    public function createService(
        #[MapRequestPayload] Config $config
    ): JsonResponse
    {
        $this->configurationProvider->setConfig($config);
        return new JsonResponse(
            new HandledResponse(
                "Success!",
                "Your configuration has been saved. To reload stylesheets and favicons, please reboot the container."
            )
        );
    }
}