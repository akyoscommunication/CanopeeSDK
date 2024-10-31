<?php

namespace Akyos\CanopeeSDK\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait CustomerCanopeeSDKTrait
{
    public const GROUP_CUSTOMER_READ = 'customer:read';

    #[ORM\Column(type: Types::JSON, nullable: true)]
    protected ?string $customerCanopee = null;

    public function getCustomerCanopee(): ?\stdClass
    {
        return json_decode($this->customerCanopee);
    }

    public function setCustomerCanopee(?string $customerCanopee): void
    {
        $this->customerCanopee = $customerCanopee;
    }
}
