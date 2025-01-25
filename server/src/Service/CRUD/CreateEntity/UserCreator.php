<?php
declare(strict_types=1);

namespace App\Service\CRUD\CreateEntity;

use App\Entity\User;
use App\Service\Ldap\LdapAggregator;

readonly class UserCreator
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string $userDn,
        private string $baseGroup,
        private string $baseDn,
        private string $principalSuffix
    ){}

    protected function esc(string $str): string{
        return ldap_escape($str, flags: LDAP_ESCAPE_FILTER);
    }

    public function create(User $user): void
    {
        // Pull out a few details to make access easier
        $username = $this->esc($user->getUserIdentifier());
        $firstName = $this->esc($user->getFirstName());
        $lastName = $this->esc($user->getLastName());
        $email = $this->esc($user->getEmail());

        // Make the new DN
        $calculatedDn = "CN=" . ldap_escape($user->getUserIdentifier(), flags: LDAP_ESCAPE_DN) . "," . $this->userDn;

        // Create and persist new user
        ldap_add(
            ldap: $this->ldapAggregator->getStockProvider(),
            dn: $calculatedDn,
            entry: [
                "mail" => [$email],
                "objectClass" => ["person", "organizationalPerson", "top", "user"],
                "objectCategory" => ["CN=Person,CN=Schema,CN=Configuration," . $this->baseDn],
                "instanceType" => ["4"],
                "distinguishedName" => [$calculatedDn],
                "loginShell" => ["/bin/bash"],
                "countryCode" => ["0"],
                "displayName" => ["$firstName $lastName"],
                "name" => ["$firstName $lastName"],
                "givenName" => [$firstName],
                "sn" => [$lastName],
                "sAMAccountName" => [$username],
                "userAccountControl" => ["512"],
                "userPrincipalName" => [$username . $this->principalSuffix],
                "lockoutTime" => ["0"]
            ]
        );

        // Ensure user's been added to the basic users group
        ldap_mod_add(
            ldap: $this->ldapAggregator->getStockProvider(),
            dn: $this->baseGroup,
            entry: [
                "member" => $calculatedDn
            ]
        );
    }
}