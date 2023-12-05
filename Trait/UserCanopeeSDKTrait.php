<?php

namespace Akyos\CanopeeSDK\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait UserCanopeeSDKTrait
{
    public const GROUP_USER_READ = 'user:read';

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true)]
    #[Groups([self::GROUP_USER_READ])]
    protected ?string $moduleToken = null;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true)]
    #[Groups([self::GROUP_USER_READ])]
    protected ?string $accessToken = null;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true)]
    #[Groups([self::GROUP_USER_READ])]
    protected ?string $refreshToken = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups([self::GROUP_USER_READ])]
    protected ?string $userCanopee = null;

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

    public function getUserCanopee(): ?string
    {
        return $this->userCanopee;
    }

    public function setUserCanopee(?string $userCanopee): void
    {
        $this->userCanopee = $userCanopee;
    }
}
