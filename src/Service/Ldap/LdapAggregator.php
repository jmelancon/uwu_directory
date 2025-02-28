<?php
declare(strict_types=1);

namespace App\Service\Ldap;

use LDAP\Connection;
use Symfony\Component\Ldap\Ldap;

class LdapAggregator
{
    private Ldap $symfonyProvider;
    private Connection $stockProvider;
    public function __construct(
        string $username,
        string $password,
        string $uri
    ){
        // Connect Symfony LDAP interface
        $this->symfonyProvider = Ldap::create(
            adapter: "ext_ldap",
            config: [
                "connection_string" => $uri,
                "options" => [
                    "protocol_version" => 3,
                    "referrals" => false,
                    "x_tls_require_cert" => false
                ]
            ]
        );
        $this->symfonyProvider->bind(
            dn: $username,
            password: $password
        );

        // Connect stock LDAP interface
        $this->stockProvider = ldap_connect($uri);
        ldap_set_option($this->stockProvider, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_bind(
            ldap: $this->stockProvider,
            dn: $username,
            password: $password,
        );
    }

    public function getStockProvider(): Connection{
        return $this->stockProvider;
    }

    public function getSymfonyProvider(): Ldap{
        return $this->symfonyProvider;
    }
}