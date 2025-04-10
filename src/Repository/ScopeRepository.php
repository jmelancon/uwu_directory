<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ScopeEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{

    /**
     * @inheritDoc
     */
    public function getScopeEntityByIdentifier(string $identifier): ?ScopeEntityInterface
    {
        $scopes = [
            'openid' => [
                'description' => 'Enable OpenID Connect support'
            ],
            'basic' => [
                'description' => 'Basic details about you',
            ],
            'email' => [
                'description' => 'Your email address',
            ],
        ];

        if (array_key_exists($identifier, $scopes) === false) {
            return null;
        }

        $scope = new ScopeEntity();
        $scope->setIdentifier($identifier);

        return $scope;
    }

    /**
     * @inheritDoc
     */
    public function finalizeScopes(array $scopes, string $grantType, ClientEntityInterface $clientEntity, ?string $userIdentifier = null, ?string $authCodeId = null): array
    {
        // idgaf
        return $scopes;
    }
}