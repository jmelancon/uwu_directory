<?php
declare(strict_types=1);

namespace App\Service\UpdateEntity;

use App\Entity\User;
use App\Exception\UsernameTakenException;
use App\Service\Condition\UserExistsCondition;
use App\Service\Ldap\LdapAggregator;

readonly class UserUpdater
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private UserExistsCondition $userExists,
        private string $userDn,
        private string $principalSuffix
    ){}

    protected function esc(string $str): string{
        return ldap_escape($str, flags: LDAP_ESCAPE_FILTER);
    }

    /**
     * Update a user entry in LDAP. Doesn't process groups.
     *
     * @param string $username
     * The username of the user to update.
     *
     * @param User $user
     * The updated user information.
     *
     * @throws UsernameTakenException
     * Happens if the username is taken on a rename.
     */
    public function update(string $username, User $user): void
    {
        $ldap = $this->ldapAggregator->getStockProvider();

        // Make sure new user doesn't exist
        if ($username !== $user->getIdentifier() && $this->userExists->check($user->getUserIdentifier()))
            throw new UsernameTakenException();

        // Sanitize vars
        $newUsername = $this->esc($user->getIdentifier());
        $email = $this->esc($user->getEmail());
        $firstName = $this->esc($user->getFirstName());
        $lastName = $this->esc($user->getLastName());

        // Make the new DNs
        $calculatedDn = "CN=" . ldap_escape($username, flags: LDAP_ESCAPE_DN) . "," . $this->userDn;

        // Modify basic attributes
        ldap_mod_replace(
            ldap: $ldap,
            dn: $calculatedDn,
            entry: [
                "mail" => [$email],
                "givenName" => [$firstName],
                "sn" => [$lastName],
                "displayName" => ["$firstName $lastName"],
            ]
        );

        // Move if needed
        if ($username !== $user->getIdentifier()){
            // Move the entry
            ldap_rename(
                ldap: $ldap,
                dn: $calculatedDn,
                new_rdn: "CN=" . ldap_escape($user->getIdentifier(), flags: LDAP_ESCAPE_DN),
                new_parent: $this->userDn,
                delete_old_rdn: true
            );

            // Update any attributes that use the username
            ldap_mod_replace(
                ldap: $ldap,
                dn: "CN=" . ldap_escape($user->getIdentifier(), flags: LDAP_ESCAPE_DN) . "," . $this->userDn,
                entry: [
                    "sAMAccountName" => [$newUsername],
                    "userPrincipalName" => [$newUsername . $this->principalSuffix]
                ]
            );
        }
    }
}