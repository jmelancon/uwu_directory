<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Exception\InvalidCredentialsException;
use App\Service\Ldap\LdapAggregator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

readonly class LdapUserProvider implements UserProviderInterface
{
    public function __construct(
        private LdapAggregator $ldap,
        private string $userDn
    ){}
    public function refreshUser(UserInterface $user): UserInterface
    {
        // TODO: Implement refreshUser() method.
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $ldapResults = $this->ldap->getSymfonyProvider()->query(
            dn: $this->userDn,
            query: "(CN=" . ldap_escape($identifier) . ")"
        )->execute();

        if ($ldapResults->count() !== 1)
            throw new InvalidCredentialsException();

        $userEntry = $ldapResults->toArray()[0];
        $ldapAttrs = $userEntry->getAttributes();

        return new User(
            identifier: $ldapAttrs["cn"][0] ?? "",
            firstName: $ldapAttrs["givenName"][0] ?? "",
            lastName: $ldapAttrs["sn"][0] ?? "",
            email: $ldapAttrs["mail"][0] ?? "",
            roleDNs: $ldapAttrs["memberOf"] ?? []
        );
    }
}