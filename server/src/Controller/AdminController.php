<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        template: "/views/admin.html.twig"
    )]
    public function index(){}
}