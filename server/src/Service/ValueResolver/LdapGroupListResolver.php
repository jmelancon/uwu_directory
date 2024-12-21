<?php

namespace App\Service\ValueResolver;

use App\Service\Ldap\LdapAggregator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

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
        $results = $query->execute();
        return [$results->toArray()];
    }
}