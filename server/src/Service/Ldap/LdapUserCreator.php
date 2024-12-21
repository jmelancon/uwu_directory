<?php

namespace App\Service\Ldap;

use App\Entity\Form\PasswordReset;
use App\Entity\Form\RegistrationAuthorization;
use App\Exception\Exception\PasswordRejectedException;

readonly class LdapUserCreator
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private LdapResetPassword $resetter,
        private string $userDn,
        private string $baseDn,
        private string $accountSuffix,
        private string $emailSuffix,
        private string $principalSuffix
    ){}

    public function create(RegistrationAuthorization $authorization, string $password): void
    {
        // Pull out a few details to make access easier
        $username = $authorization->getInitialRequest()->getIdentifier();
        $firstName = $authorization->getInitialRequest()->getFirstName();
        $lastName = $authorization->getInitialRequest()->getLastName();

        // Make the new DN
        $calculatedDn = "CN=" . $username . "$this->accountSuffix," . $this->userDn;

        // Create and persist new user
        ldap_add(
            ldap: $this->ldapAggregator->getStockProvider(),
            dn: $calculatedDn,
            entry: [
                "mail" => [$username . $this->emailSuffix],
                "objectClass" => ["person", "organizationalPerson", "top", "user"],
                "objectCategory" => ["CN=Person,CN=Schema,CN=Configuration," . $this->baseDn],
                "instanceType" => ["4"],
                "distinguishedName" => [$calculatedDn],
                "loginShell" => ["/bin/bash"],
                "countryCode" => ["0"],
                "displayName" => ["$firstName $lastName$this->accountSuffix"],
                "name" => ["$firstName $lastName"],
                "givenName" => [$firstName],
                "sn" => [$lastName],
                "sAMAccountName" => [$username . $this->accountSuffix],
                "userAccountControl" => ["512"],
                "userPrincipalName" => [$username . $this->principalSuffix],
                "lockoutTime" => ["0"]
            ]
        );

        // Attempt a password reset. If it fails, we've gotta remove the user entry and get a new
        // password from the user.
        try{
            $rq = new PasswordReset();
            $rq->setIdentifier($username);

            $this->resetter->reset(
                $rq,
                $password
            );
        }
        catch (\Throwable) {
            ldap_delete(
                ldap: $this->ldapAggregator->getStockProvider(),
                dn: $calculatedDn
            );
            throw new PasswordRejectedException;
        }

        // Add user to each assigned group
        foreach($authorization->getGrantedDns() as $group){
            ldap_mod_add(
                ldap: $this->ldapAggregator->getStockProvider(),
                dn: $group,
                entry: [
                    "member" => $calculatedDn
                ]
            );
        }
    }
}