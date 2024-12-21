<?php

namespace App\Service\Ldap;

readonly class LdapGroupModifier
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private LdapGetUserGroups $groups,
        private string $userDn,
        private string $usernameSuffix
    ){}

    public function write(string $username, array $newGroups): void
    {
        // Make the new DN
        $calculatedDn = "CN=" . $username . "$this->usernameSuffix," . $this->userDn;

        // Fetch existing group memberships and parse the new memberships
        $existing = $this->groups->fetch($username);
        $new = array_keys($newGroups);

        // Filter out groups that won't be changed, get add/remove lists
        $add = array_diff($new, $existing);
        $del = array_diff($existing, $new);

        // Apply additions
        foreach($add as $groupDn){
            ldap_mod_add(
                ldap: $this->ldapAggregator->getStockProvider(),
                dn: $groupDn,
                entry: [
                    "member" => $calculatedDn
                ]
            );
        }

        // Apply deletions
        foreach($del as $groupDn){
            ldap_mod_del(
                ldap: $this->ldapAggregator->getStockProvider(),
                dn: $groupDn,
                entry: [
                    "member" => $calculatedDn
                ]
            );
        }
    }
}