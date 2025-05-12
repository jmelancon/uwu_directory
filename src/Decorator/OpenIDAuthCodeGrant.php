<?php
declare(strict_types=1);

namespace App\Decorator;

use DateInterval;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use League\OAuth2\Server\ResponseTypes\RedirectResponse;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Nyholm\Psr7\Response;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Request;

#[AsDecorator(AuthCodeGrant::class)]
class OpenIDAuthCodeGrant extends AuthCodeGrant
{
    public function __construct(
        AuthCodeRepositoryInterface $authCodeRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        string $authCodeTTL
    ) {
        parent::__construct($authCodeRepository, $refreshTokenRepository, new DateInterval($authCodeTTL));
    }

    /**
     * Decorate AuthCodeGrant so we can support the 'nonce' parameter
     *
     * @see https://github.com/steverhoades/oauth2-openid-connect-server/issues/47#issuecomment-1228370632
     * @see https://github.com/steverhoades/oauth2-openid-connect-server/issues/47#issuecomment-1229195731
     *
     * @param AuthorizationRequestInterface $authorizationRequest
     * @return ResponseTypeInterface
     * @throws OAuthServerException
     */
    public function completeAuthorizationRequest(
        AuthorizationRequestInterface $authorizationRequest
    ): ResponseTypeInterface {
        $request = Request::createFromGlobals();
        $responseInterface =  parent::completeAuthorizationRequest($authorizationRequest);
        $nonce = $request->get('nonce');

        if ($responseInterface instanceof RedirectResponse && $nonce){
            $response = $responseInterface->generateHttpResponse(new Response());

            // Dissect response
            $redirectUri = $response->getHeader('Location');
            $parsed = parse_url($redirectUri[0]);
            parse_str($parsed['query'], $query);

            // Patch in nonce
            $payload = json_decode(
                json: $this->decrypt($query['code']),
                associative: true
            );
            $payload['nonce'] = $nonce;

            // Rebuild response
            $uri = $authorizationRequest->getRedirectUri()
                ?? $this->getClientRedirectUri($authorizationRequest->getClient());
            $responseInterface->setRedirectUri(
                $this->makeRedirectUri(
                    uri: $uri,
                    params: [
                        'state' => $authorizationRequest->getState(),
                        'code' => $this->encrypt(json_encode($payload))
                    ]
                )
            );
        }

        return $responseInterface;
    }
}