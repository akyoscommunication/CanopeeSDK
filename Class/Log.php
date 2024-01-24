<?php

namespace Akyos\CanopeeSDK\Class;

use Akyos\CanopeeModuleSDK\Class\Post;
use Akyos\CanopeeSDK\Class\LogInterface;
use Akyos\CanopeeSDK\Enum\LogType;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Log extends Post implements LogInterface
{
    public const LOG_DOMAIN = 'log';

    public mixed $sender = null;
    public LogType $type;
    public ?string $tokenTraduction = null;
    public ?array $args = [];
    public ?string $content = null;
    public ?string $attachment = null;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly TranslatorInterface $translator,
    )
    {
        parent::__construct('logs');
    }

    public function onPreQuery(): void
    {
        $this->prepareQueryParams();
        $this->prepareBody();

        parent::onPreQuery();
    }

    public function onSetData(): void
    {
        // Blank method
    }

    // GETTERS & SETTERS

    public function getSender(): mixed
    {
        return $this->sender;
    }

    public function setSender(mixed $sender): self
    {
        if($this->sender instanceof ($this->container->getParameter('entity')['user_entity'])){
            $this->sender = $this->sender->getUuid();
        } else {
            $this->sender = $sender;
        }

        return $this;
    }

    public function getType(): LogType
    {
        return $this->type;
    }

    public function setType(LogType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTokenTraduction(): string
    {
        return $this->tokenTraduction;
    }

    public function setTokenTraduction(string $tokenTraduction): self
    {
        $trans = $this->translator->trans($tokenTraduction, $this->args, self::LOG_DOMAIN);
        if ($trans === $tokenTraduction) {
            throw new InvalidArgumentException($this->translator->trans('log.error.tokenTraduction', ['tokenTraduction' => $tokenTraduction], self::LOG_DOMAIN));
        }

        $this->tokenTraduction = $tokenTraduction;
        $this->content = $trans;

        return $this;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function setArgs(array $args): self
    {
        $updatedArgs = [];
        foreach ($args as $key => $arg) {
            $updatedArgs['%'.$key.'%'] = $arg;
        }
        $this->args = $updatedArgs;

        return $this;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function setAttachment(?string $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function prepareQueryParams(): self
    {
        $queryParams = [
            'fromModule' => $this->container->getParameter('module_slug'),
            'sender' => $this->sender?->getUuid(),
            'type' => $this->type->value,
            'tokenTraduction' => $this->tokenTraduction,
        ];

        $this->setQueryParams($queryParams);

        return $this;
    }

    public function prepareBody(): self
    {
        $body = [];
        $body['content'] = $this->content;
        $body['args'] = $this->args;
        if($this->attachment){
            $body['attachement'] = $this->attachment;
        }
        $this->body = $body;

        return $this;
    }
}
