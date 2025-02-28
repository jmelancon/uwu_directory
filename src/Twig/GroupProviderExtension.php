<?php
declare(strict_types=1);

namespace App\Twig;

use App\Service\CRUD\ReadEntity\ReadGroups;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GroupProviderExtension extends AbstractExtension
{
    public function __construct(
        private readonly ReadGroups $readGroups,
    ){}
    public function getFunctions(): array
    {
        return [
            new TwigFunction('allGroups', $this->readGroups->fetch(...)),
        ];
    }
}