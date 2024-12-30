<?php

namespace App\Controller;

use App\Entity\Response\HandledResponse;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Ldap\Security\LdapUser;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

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

    #[Route(
        path: "/login",
        name: "loginForm",
        methods: ["GET"]
    )]
    #[Template(
        template: "login.html.twig"
    )]
    public function login(){}

    #[Route(
        path: "/login/submit",
        name: "loginSubmit",
        methods: ["POST"]
    )]
    public function loggedIn(){}
}