<?php
declare(strict_types=1);

namespace App\Service\ValueResolver;

use App\Exception\TokenMissingException;
use App\Service\Tokenizer;
use ParagonIE\Paseto\Exception\InvalidVersionException;
use ParagonIE\Paseto\Exception\PasetoException;
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
     * Take a Paseto token from query params and decode it.
     *
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     *
     * @throws TokenMissingException
     * @throws InvalidVersionException
     * @throws PasetoException
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