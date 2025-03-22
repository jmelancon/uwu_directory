<?php
declare(strict_types=1);

namespace App\Entity;

use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\Entities\ClaimSetInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;

class User implements UserInterface, ClaimSetInterface, UserEntityInterface
{
    public function __construct(
        private string $identifier,
        private string $firstName,
        private string $lastName,
        private string $email,
        /** @var array<string> roleDNs */
        private array $roleDNs = []
    ){}

    public function getRoleDNs(): array
    {
        return $this->roleDNs;
    }

    public function setRoleDNs(array $roleDNs): void
    {
        $this->roleDNs = $roleDNs;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    #[Ignore]
    public function getRoles(): array
    {
        return array_merge(
            array_map(
                function(string $fqdn){
                    // Get the group CN
                    $cn = preg_replace(
                        pattern: '/^CN=([^,]+).*$/i',
                        replacement: '$1',
                        subject: $fqdn,
                    );

                    // Filter out illegal chars, swap spaces for underscores, uppercase
                    return "ROLE_" . str_replace(
                        search: " ",
                        replace: "_",
                        subject: strtoupper(
                            string: preg_replace(
                                pattern: '/[^[:alpha:][:space:]]/u',
                                replacement: "",
                                subject: $cn
                            )
                        )
                    );
                },
                array: $this->roleDNs
            ),
            ["ROLE_USER"]
        );
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    #[Ignore]
    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function getClaims(): array
    {
        return [
            'name' => "$this->firstName $this->lastName",
            'family_name' => $this->lastName,
            'given_name' => $this->firstName,
            'preferred_username' => $this->identifier,
            'locale' => 'US',
            'email' => $this->email,
            'email_verified' => true,
        ];
    }
}