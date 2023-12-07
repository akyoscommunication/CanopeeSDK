<?php

namespace Akyos\CanopeeSDK\Service;

use Akyos\CanopeeSDK\Service\ProviderService;
use Akyos\CanopeeSDK\Enum\LogType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LogService
{
    public const LOG_DOMAIN = 'log';

    public function __construct(
        private readonly ProviderService $providerService,
        private readonly TranslatorInterface $translator,
        private readonly ContainerInterface $container,
    ){
    }

    public function add($sender, LogType $type, string $tokenTraduction, array $args, string $attachment = null): void
    {
        $updatedArgs = [];
        foreach ($args as $key => $arg) {
            $updatedArgs['%'.$key.'%'] = $arg;
        }

        if ($this->translator->trans($tokenTraduction, $updatedArgs, self::LOG_DOMAIN) === $tokenTraduction) {
            throw new \InvalidArgumentException($this->translator->trans('log.error.tokenTraduction', ['tokenTraduction' => $tokenTraduction], self::LOG_DOMAIN));
        }

        $query = $this->providerService->new('log/new', 'POST');
        $query->setPathParams([
            'slug' => $this->container->getParameter('module_slug')
        ]);
        if($sender instanceof ($this->container->getParameter('entity')['user_entity'])){
            $sender = $sender->getUuid();
        }
        $query->setQueryParams([
            'sender' => $sender,
            'type' => $type->value,
            'tokenTraduction' => $tokenTraduction,
        ]);

        $body = [];
        $body['content'] = $this->translator->trans($tokenTraduction, $updatedArgs, self::LOG_DOMAIN);
        $body['args'] = $updatedArgs;
        if($attachment){
            $body['attachement'] = $attachment;
        }
        $query->setBody($body);
        $result = $query->getResults();
    }
}
