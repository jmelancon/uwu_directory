<?php
declare(strict_types=1);

namespace App\Service\ValueResolver;

use App\Service\CRUD\ReadEntity\ReadGroups;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[AsTargetedValueResolver(LdapGroupListResolver::class)]
readonly class LdapGroupListResolver implements ValueResolverInterface
{
    public function __construct(
        private ReadGroups $readGroups,
    ){}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        return [$this->readGroups->fetch()];
    }
}