<?php
declare(strict_types=1);

namespace App\Service\ValueResolver;

use App\Service\Ldap\LdapAggregator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Ldap\Entry;

#[AsTargetedValueResolver(LdapGroupListResolver::class)]
readonly class LdapGroupListResolver implements ValueResolverInterface
{
    public function __construct(
        private LdapAggregator $ldapAggregator,
        private string $baseDn
    ){}


    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $query = $this->ldapAggregator->getSymfonyProvider()->query($this->baseDn, '(objectclass=group)');

        /** @var $groups array<Entry> */
        $groups = $query->execute()->toArray();

        return [
            array_filter(
                $groups,
                function (Entry $e){
                    return !str_starts_with($e->getDn(), "CN=Basic Users,");
                }
            )
        ];
    }
}