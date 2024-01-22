<?php

namespace Akyos\CanopeeSDK\Service;

use Akyos\CanopeeModuleSDK\Service\ProviderService;
use Akyos\CanopeeSDK\Class\LogInterface;
use Akyos\CanopeeSDK\Enum\LogType;

class LogService
{
    public const LOG_DOMAIN = 'log';

    public function __construct(
        private readonly ProviderService $provider,
        private readonly LogInterface $log,
    ){
    }

    public function add($sender, LogType $type, string $tokenTraduction, array $args, string $attachment = null): void
    {
        $query = $this->log
            ->setSender($sender)
            ->setType($type)
            ->setArgs($args)
            ->setTokenTraduction($tokenTraduction)
            ->setAttachment($attachment)
        ;

        $this->provider->initialize('canopee')->send($query);
    }
}
