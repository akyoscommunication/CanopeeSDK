<?php

namespace Akyos\CanopeeSDK\Webhook\Consumer;

use Akyos\CanopeeSDK\Service\CanopeeUserSyncService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Serializer\SerializerInterface;

#[AsRemoteEventConsumer(name: 'sync_user')]
readonly class SyncUserEventConsumer implements ConsumerInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager,
        private CanopeeUserSyncService $canopeeUserSyncService,
    )
    {

    }
    public function consume(RemoteEvent $event): void
    {
        $userCanopee = $this->serializer->serialize($event->getPayload(), 'json');
        $userCanopee = json_decode($userCanopee);
        $this->canopeeUserSyncService->createOrUpdateFromCanopee($userCanopee);
    }
}
