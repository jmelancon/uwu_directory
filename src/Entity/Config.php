<?php
declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class Config{
    #[Assert\Regex(
        pattern: '/^@.+?\..+$/',
        message: "The provided suffix does not follow proper formatting."
    )]
    private ?string $emailSuffix = null;

    private ?string $parentOrgAccountDescription = null;

    #[Assert\CssColor(
        formats: ["hex_long", "hex_short", "basic_named_colors", "extended_named_colors"],
    )]
    private ?string $accentColor = null;

    private ?string $organization = null;

    private string $service = "uwu_directory";

    private ?string $emailSignature = null;

    private ?string $favicon = null;

    public function getEmailSignature(): ?string
    {
        return $this->emailSignature;
    }

    public function setEmailSignature(?string $emailSignature): void
    {
        $this->emailSignature = $emailSignature;
    }

    public function getEmailSuffix(): ?string
    {
        return $this->emailSuffix;
    }

    public function setEmailSuffix(?string $emailSuffix): void
    {
        $this->emailSuffix = $emailSuffix;
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

    public function getAccentColor(): ?string
    {
        return $this->accentColor;
    }

    public function setAccentColor(?string $accentColor): void
    {
        $this->accentColor = $accentColor;
    }
}