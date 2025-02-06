<?php
declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class Config{
    #[Groups("generalConfig")]
    #[Assert\Regex(
        pattern: '/^@.+?\..+$/',
        message: "The provided suffix does not follow proper formatting."
    )]
    private ?string $emailSuffix = null;

    private string $customScss =  "// Custom SCSS for uwu_directory\n\n"
                                . "// Site logo (SVG formatted, 256px by 256px viewport size)\n"
                                . "// \$site_logo: url('data:image/svg+xml,<svg>...</svg>');\n\n"
                                . "// Site primary color\n"
                                . "// \$primary: #c950a7;\n";

    #[Groups("generalConfig")]
    private ?string $organization = null;

    #[Groups("generalConfig")]
    private string $service = "uwu_directory";

    #[Groups("generalConfig")]
    public function getEmailSuffix(): ?string
    {
        return $this->emailSuffix;
    }

    public function setEmailSuffix(?string $emailSuffix): void
    {
        $this->emailSuffix = $emailSuffix;
    }

    public function getCustomScss(): string
    {
        return $this->customScss;
    }

    public function setCustomScss(string $customScss): void
    {
        $this->customScss = $customScss;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function setOrganization(?string $organization): void
    {
        $this->organization = $organization;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): void
    {
        $this->service = $service;
    }
}