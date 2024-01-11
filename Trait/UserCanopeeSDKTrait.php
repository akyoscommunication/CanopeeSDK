<?php

namespace Akyos\CanopeeSDK\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait UserCanopeeSDKTrait
{
    public const GROUP_USER_READ = 'user:read';

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups([self::GROUP_USER_READ])]
    protected ?string $userCanopee = null;

    public function getUserCanopee(): ?\stdClass
    {
        return json_decode($this->userCanopee);
    }

    public function setUserCanopee(?string $userCanopee): void
    {
        $this->userCanopee = $userCanopee;
    }
}
