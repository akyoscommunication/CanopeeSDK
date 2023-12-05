<?php

namespace Akyos\CanopeeSDK\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait CustomerCanopeeSDKTrait
{
    public const GROUP_CUSTOMER_READ = 'customer:read';

    #[ORM\Column]
    #[Groups([self::GROUP_CUSTOMER_READ])]
    private ?int $canopeeId = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]

    protected ?string $customerCanopee = null;

    public function getCustomerCanopee(): ?string
    {
        return $this->customerCanopee;
    }

    public function setCustomerCanopee(?string $customerCanopee): void
    {
        $this->customerCanopee = $customerCanopee;
    }

    public function getCanopeeId(): ?int
    {
        return $this->canopeeId;
    }

    public function setCanopeeId(?int $canopeeId): void
    {
        $this->canopeeId = $canopeeId;
    }
}
