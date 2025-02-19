<?php

namespace Akyos\CanopeeSDK\Service;

use Akyos\CanopeeModuleSDK\Class\AbstractQuery;
use Akyos\CanopeeModuleSDK\Service\ProviderService;
use Akyos\CanopeeSDK\Class\LogInterface;
use Akyos\CanopeeSDK\Enum\LogType;
use App\Entity\User;
use App\Entity\UserAccessRight;

class LogService
{
    public const LOG_DOMAIN = 'log';

    public function __construct(
        private readonly ProviderService $provider,
        private readonly LogInterface $log,
    ){
    }

    public function add($sender, LogType $type, string $tokenTraduction, array $args, UserAccessRight $context, string $attachment = null, ?User $user = null): AbstractQuery
    {
        $query = $this->log
            ->setSender($sender)
            ->setType($type)
            ->setArgs($args)
            ->setContext($context)
            ->setTokenTraduction($tokenTraduction)
            ->setAttachment($attachment)
        ;

        return $this->provider->initialize('canopee', $user)->send($query);
    }
}
