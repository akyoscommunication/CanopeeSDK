<?php

namespace Akyos\CanopeeSDK;

use Akyos\CanopeeSDK\DependencyInjection\CanopeeSDKExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CanopeeSDK extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new CanopeeSDKExtension();
        }
        return $this->extension;
    }
}