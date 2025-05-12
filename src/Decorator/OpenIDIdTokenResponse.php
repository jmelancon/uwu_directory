<?php
declare(strict_types=1);

namespace App\Decorator;

use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\IdTokenResponse;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Request;

#[AsDecorator(IdTokenResponse::class)]
class OpenIDIdTokenResponse extends IdTokenResponse
{
    protected function getBuilder(
        AccessTokenEntityInterface $accessToken,
        UserEntityInterface $userEntity): Builder
    {
        $claimsFormatter = ChainedFormatter::withUnixTimestampDates();
        $builder = new Builder(new JoseEncoder(), $claimsFormatter);

        // Get nonce, or lack thereof
        $nonce = json_decode(
            json: $this->decrypt(Request::createFromGlobals()->get('code')),
            associative: true
        )['nonce'] ?? null;

        // Add required id_token claims
        return $builder
            ->permittedFor($accessToken->getClient()->getIdentifier())
            ->issuedBy('https://' . $_SERVER['HTTP_HOST'])
            ->issuedAt(new \DateTimeImmutable())
            ->expiresAt($accessToken->getExpiryDateTime())
            ->relatedTo($userEntity->getIdentifier())
            ->withClaim('nonce', $nonce);
    }
}