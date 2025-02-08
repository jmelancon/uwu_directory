<?php
declare(strict_types=1);

namespace App\Service\Ldap;

use Throwable;

readonly class LdapBindAuthentication
{
    public function __construct(
        private string $userDn,
        private string $connectionString,
    )
    {
    }

    /**
     * Check if a user may log in by checking if they can bind to LDAP.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function auth(string $username, string $password): bool{
        try {
            $escUser = ldap_escape($username);
            return ldap_bind(
                ldap_connect(
                    uri: $this->connectionString,
                ),
                dn: "CN=$escUser,$this->userDn",
                password: $password
            );
        } catch (Throwable) {
            return false;
        }
    }
}