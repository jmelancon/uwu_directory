<?php
declare(strict_types=1);

namespace App\Service;

use DateInterval;
use DateTime;
use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Exception\InvalidKeyException;
use ParagonIE\Paseto\Exception\InvalidPurposeException;
use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Keys\Version4\SymmetricKey;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Protocol\Version4;
use ParagonIE\Paseto\ProtocolCollection;
use ParagonIE\Paseto\Purpose;
use ParagonIE\Paseto\Rules\IssuedBy;
use ParagonIE\Paseto\Rules\ValidAt;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use ValueError;

class Tokenizer
{
    private SymmetricKey $key;
    private Serializer $serializer;
    public function __construct(
        string $secret,
    ){
        $this->key = new SymmetricKey($secret);
        $this->serializer = new Serializer(
            [
                new ObjectNormalizer(
                    null,
                    null,
                    null,
                    new ReflectionExtractor()
                )
            ],
            [
                new JsonEncoder()
            ]
        );
    }

    /**
     * Serialize any serializable object and encode it in a Paseto
     * token.
     *
     * @param object $source
     * The source object. Must be serializable by the Symfony Serializer.
     *
     * @return string
     * The encoded Paseto token.
     *
     * @throws InvalidKeyException
     * @throws InvalidPurposeException
     * @throws PasetoException
     * @throws ExceptionInterface
     */
    public function encode(object $source): string{
        // Serialize
        $serialized = $this->serializer->serialize(
            data: $source,
            format: 'json'
        );

        // Create token
        $token = (new Builder())
            ->setKey($this->key)
            ->setVersion(new Version4)
            ->setPurpose(Purpose::local())
            // Set it to expire in one day
            ->setIssuedAt()
            ->setNotBefore()
            ->setExpiration(
                (new DateTime())->add(new DateInterval('P01D'))
            )
            ->setIssuer("your mother")
            // Store arbitrary data
            ->setClaims(
                [
                    "obj" => $serialized,
                    "class" => get_class($source)
                ]
            );

        return (string) $token;
    }

    public function decode(string $encrypted): object{
        $parser = (new Parser())
            ->setKey($this->key)
            // Adding rules to be checked against the token
            ->addRule(new ValidAt)
            ->addRule(new IssuedBy('your mother'))
            ->setPurpose(Purpose::local())
            // Only allow version 4
            ->setAllowedVersions(ProtocolCollection::v4());

        $parsed = $parser->parse($encrypted);

        if (!($parsed->has("obj") && $parsed->has("class")))
            throw new ValueError("Decoded token is incomplete!");

        return $this->serializer->deserialize(
            data: $parsed->get("obj"),
            type: $parsed->get("class"),
            format: "json",
        );
    }
}