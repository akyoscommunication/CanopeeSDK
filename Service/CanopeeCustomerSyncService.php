<?php

namespace Akyos\CanopeeSDK\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Akyos\CanopeeSDK\Service\ProviderService;


class CanopeeCustomerSyncService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContainerInterface $container,
        private readonly RequestStack $requestStack,
        private readonly ProviderService $providerService,
        private readonly Security $security
    )
    {
    }

    public function createOrUpdateFormCanopee(?object $customerCanopee){

        if(!$customer = $this->entityManager->getRepository($this->container->getParameter('entity')['customer_entity'])->findOneBy(['canopeeId' => $customerCanopee->id])){
            $customer = new ($this->container->getParameter('entity')['customer_entity'])();
            $customer->setCanopeeId($customerCanopee->id);
        }
        $customer->setCustomerCanopee(json_encode($customerCanopee));

        if($customerCanopee->deletedState === 'delete'){
            $this->entityManager->getRepository($this->container->getParameter('entity')['customer_entity'])->remove($customer, true);
        }

        $this->entityManager->getRepository($this->container->getParameter('entity')['customer_entity'])->add($customer, true);

        return $customer;
    }
}