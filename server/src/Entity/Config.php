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

    #[Groups("generalConfig")]
    private ?string $parentOrgAccountDescription = null;

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
    private ?string $emailSignature = null;

    #[Groups("generalConfig")]
    private ?string $emailStyles = null;

    private ?string $favicon = null;

    public function getEmailSignature(): ?string
    {
        return $this->emailSignature;
    }

    public function setEmailSignature(?string $emailSignature): void
    {
        $this->emailSignature = $emailSignature;
    }

    public function getEmailStyles(): ?string
    {
        return $this->emailStyles;
    }

    public function setEmailStyles(?string $emailStyles): void
    {
        $this->emailStyles = $emailStyles;
    }

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

    public function getParentOrgAccountDescription(): ?string
    {
        return $this->parentOrgAccountDescription;
    }

    public function setParentOrgAccountDescription(?string $parentOrgAccountDescription): void
    {
        $this->parentOrgAccountDescription = $parentOrgAccountDescription;
    }

    public function getFavicon(): ?string
    {
        return $this->favicon;
    }

    public function setFavicon(?string $favicon): void
    {
        $this->favicon = $favicon;
    }
}