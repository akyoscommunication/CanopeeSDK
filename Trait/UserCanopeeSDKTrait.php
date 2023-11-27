<?php

namespace Akyos\CanopeeSDK\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait UserCanopeeSDKTrait
{
    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true)]
    protected ?string $moduleToken = null;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true)]
    protected ?string $accessToken = null;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true)]
    protected ?string $refreshToken = null;

    public function getModuleToken(): ?string
    {
        return $this->moduleToken;
    }

    public function setModuleToken(?string $moduleToken): void
    {
        $this->moduleToken = $moduleToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }
}
