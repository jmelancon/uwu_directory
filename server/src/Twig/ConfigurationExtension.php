<?php
declare(strict_types=1);

namespace App\Twig;

use App\Service\ConfigurationProvider;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ConfigurationExtension extends AbstractExtension
{
    public function __construct(
        private readonly ConfigurationProvider $configurationProvider,
        private readonly PropertyAccessorInterface $propertyAccessor,
    ){}
    public function getFunctions(): array
    {
        return [
            new TwigFunction('config', $this->fetchConfigProperty(...)),
        ];
    }

    public function fetchConfigProperty(string $key): ?string
    {
        return $this->propertyAccessor->getValue($this->configurationProvider->getConfig(), $key);
    }
}