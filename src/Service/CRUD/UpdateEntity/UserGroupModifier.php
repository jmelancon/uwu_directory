<?php
declare(strict_types=1);

namespace App\Service\CRUD\UpdateEntity;

use App\Service\CRUD\ReadEntity\ReadUserGroups;
use App\Service\Ldap\LdapAggregator;

readonly class UserGroupModifier
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private ReadUserGroups $groups,
        private string         $userDn,
        private string         $groupDn
    ){}

    private function executeAddition(string $userDn, string $groupDn): void{
        ldap_mod_add(
            ldap: $this->ldapAggregator->getStockProvider(),
            dn: $groupDn,
            entry: [
                "member" => $userDn
            ]
        );
    }

    private function executeDeletion(string $userDn, string $groupDn): void{
        ldap_mod_del(
            ldap: $this->ldapAggregator->getStockProvider(),
            dn: $groupDn,
            entry: [
                "member" => $userDn
            ]
        );
    }

    public function batch(string $username, array $newGroups): void
    {
        // Make the new DN
        $calculatedDn = "CN=" . ldap_escape($username, flags: LDAP_ESCAPE_DN) . "," . $this->userDn;

        // Fetch existing group memberships and parse the new memberships
        $existing = $this->groups->fetch($username) ?? [];
        $new = array_keys($newGroups);

        // Ensure that the "Basic Users" group is applied at all times
        if (!in_array("CN=Basic Users,$this->groupDn", $new))
            $new[] = "CN=Basic Users,$this->groupDn";

        // Filter out groups that won't be changed, get add/remove lists
        $add = array_diff($new, $existing);
        $del = array_diff($existing, $new);

        // Apply additions
        foreach($add as $groupDn){
            $this->executeAddition($calculatedDn, $groupDn);
        }

        // Apply deletions
        foreach($del as $groupDn){
            $this->executeDeletion($calculatedDn, $groupDn);
        }
    }

    public function add(string $username, string $group): void{
        $userDn = "CN=" . ldap_escape($username, flags: LDAP_ESCAPE_DN) . "," . $this->userDn;
        $groupDn = "CN=" . ldap_escape($group, flags: LDAP_ESCAPE_DN) . "," . $this->groupDn;
        $this->executeAddition($userDn, $groupDn);
    }

    public function delete(string $username, string $group): void{
        $userDn = "CN=" . ldap_escape($username, flags: LDAP_ESCAPE_DN) . "," . $this->userDn;
        $groupDn = "CN=" . ldap_escape($group, flags: LDAP_ESCAPE_DN) . "," . $this->groupDn;
        $this->executeDeletion($userDn, $groupDn);
    }
}