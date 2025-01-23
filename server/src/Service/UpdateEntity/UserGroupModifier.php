<?php
declare(strict_types=1);

namespace App\Service\UpdateEntity;

use App\Service\Ldap\LdapAggregator;
use App\Service\ReadEntity\ReadUserGroups;

readonly class UserGroupModifier
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private ReadUserGroups $groups,
        private string         $userDn,
        private string         $groupDn
    ){}

    public function write(string $username, array $newGroups): void
    {
        // Make the new DN
        $calculatedDn = "CN=" . ldap_escape($username) . "," . $this->userDn;

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