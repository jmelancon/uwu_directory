<?php

namespace App\Service\ValueResolver;

use App\Exception\Exception\TokenMissingException;
use App\Service\Tokenizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[AsTargetedValueResolver(DecodedObjectResolver::class)]
readonly class DecodedObjectResolver implements ValueResolverInterface
{
    public function __construct(
        private Tokenizer $tokenizer
    ){}

    /**
     * @inheritDoc
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $queryParam = $request->get("token") ?? $request->query->get("token") ?? null;
        if (!$queryParam){
            throw new TokenMissingException();
        }

        return [$this->tokenizer->decode($queryParam)];
    }
}