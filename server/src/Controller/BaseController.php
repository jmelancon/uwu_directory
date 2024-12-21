<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/")]
class BaseController extends AbstractController
{
    #[Route(
        name: "root",
        path: "/",
        methods: ["GET"]
    )]
    #[Template(
        template: "index.html.twig"
    )]
    public function root(){}
}