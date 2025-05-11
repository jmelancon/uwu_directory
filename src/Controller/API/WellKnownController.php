<?php
declare(strict_types=1);

namespace App\Controller\API;

use OpenSSLAsymmetricKey;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: '/.well-known',
    name: 'api.well-known',
    format: 'json'
)]
#[IsGranted('PUBLIC_ACCESS')]
class WellKnownController extends AbstractController
{
    private OpenSSLAsymmetricKey $pubkey;
    public function __construct(
        string $publicKeyPath,
        private readonly string $issuer
    ){
        $this->pubkey = openssl_pkey_get_public(public_key: "file://$publicKeyPath");
    }

    #[Route(
        path: '/openid-configuration',
        name: '.openid-configuration',
    )]
    public function openidConfiguration(): JsonResponse{
        return new JsonResponse(
            data: [
                'issuer' => $this->issuer,
                'authorization_endpoint' => $this->generateUrl(
                    route: 'oauth2_authorize',
                    referenceType: UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'jwks_uri' => $this->generateUrl(
                    route: 'api.well-known.jwks',
                    referenceType: UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'token_endpoint' => $this->generateUrl(
                    route: 'oauth2_token',
                    referenceType: UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'response_types_supported' => [
                    "code",
                    "code id_token",
                    "id_token",
                    "id_token token"
                ],
                'subject_types_supported' => [
                    'pairwise'
                ],
                'id_token_signing_alg_values_supported' => [
                    'RS256'
                ],
                'scopes_supported' => [
                    'openid',
                    'email',
                    'profile'
                ],
                'claims_supported' => [
                    "sub",
                    "iss",
                    "auth_time",
                    "acr",
                    "name",
                    "given_name",
                    "family_name",
                    "nickname",
                    "profile",
                    "email"
                ]
            ],
            status: Response::HTTP_OK
        );
    }

    #[Route(
        path: '/jwks',
        name: '.jwks',
    )]
    public function jwks(): JsonResponse{
        $keyDetails = openssl_pkey_get_details($this->pubkey);
        $kid = sha1($keyDetails['key']);
        $n = base64_encode($keyDetails['rsa']['n']);
        $e = base64_encode($keyDetails['rsa']['e']);

        if ($kid && $n && $e){
            return new JsonResponse(
                data: [
                    'keys' => [
                        [
                            'kid' => $kid,
                            'kty' => 'RSA',
                            'alg' => 'RS256',
                            'use' => 'sig',
                            'n' => $n,
                            'e' => $e,
                        ]
                    ]
                ],
                status: Response::HTTP_OK
            );
        } else {
            return new JsonResponse(
                data: [],
                status: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}