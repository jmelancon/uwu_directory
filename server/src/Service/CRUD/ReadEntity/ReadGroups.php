<?php
declare(strict_types=1);

namespace App\Service\CRUD\ReadEntity;

use App\Service\Ldap\LdapAggregator;
use Symfony\Component\Ldap\Entry;

readonly class ReadGroups
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string         $baseDn,
    ){}

    public function fetch(): ?array{
        $query = $this->ldapAggregator->getSymfonyProvider()->query($this->baseDn, '(objectclass=group)');

        /** @var $groups array<Entry> */
        $groups = $query->execute()->toArray();

        return array_filter(
            $groups,
            function (Entry $e){
                return !str_starts_with($e->getDn(), "CN=Basic Users,");
            }
        );
    }
}