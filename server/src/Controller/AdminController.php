<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\ValueResolver\LdapGroupListResolver;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/admin")]
#[IsGranted("ROLE_SSO_ADMINISTRATORS")]
class AdminController extends AbstractController
{
    #[Route(
        path: "/",
        name: "administratorIndex",
        methods: ["GET"]
    )]
    #[Template(
        template: "/views/admin.html.twig",
        vars: ["groups" => "groups"]
    )]
    public function index(
        #[ValueResolver(LdapGroupListResolver::class)] array $groups
    ): void
    {}
}