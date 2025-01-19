<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/")]
class BaseController extends AbstractController
{
    #[Route(
        path: "/",
        name: "root",
        methods: ["GET"]
    )]
    #[Template(
        template: "/views/index.html.twig"
    )]
    public function root(){}

    #[Route(
        path: "/login",
        name: "loginForm",
        methods: ["GET"]
    )]
    #[Template(
        template: "/views/login.html.twig"
    )]
    public function login(){}

    #[Route(
        path: "/login/submit",
        name: "loginSubmit",
        methods: ["POST"]
    )]
    public function loggedIn(){}

    #[Route(
        path: "/logout",
        name: "logout",
        methods: ["GET"]
    )]
    public function logout(
        SessionInterface $session
    ): Response
    {
        // Redirect to root
        $response = new RedirectResponse(
            url: $this->generateUrl(
                route: "root"
            )
        );

        // Clear cookies
        $session->clear();

        // Add flash
        $this->addFlash(
            type: "info",
            message:"You have been logged out."
        );

        return $response;
    }
}