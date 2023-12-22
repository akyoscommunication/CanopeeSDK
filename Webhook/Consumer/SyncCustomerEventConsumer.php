<?php

namespace Akyos\CanopeeSDK\Webhook\Consumer;

use Akyos\CanopeeSDK\Service\CanopeeCustomerSyncService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Serializer\SerializerInterface;

#[AsRemoteEventConsumer(name: 'sync_customer')]
readonly class SyncCustomerEventConsumer implements ConsumerInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager,
        private CanopeeCustomerSyncService $canopeeCustomerSyncService,
    )
    {

    }
    public function consume(RemoteEvent $event): void
    {
        $customerCanopee = $this->serializer->serialize($event->getPayload(), 'json');
        $customerCanopee = json_decode($customerCanopee);
        $this->canopeeCustomerSyncService->createOrUpdateFromCanopee($customerCanopee);
    }
}
