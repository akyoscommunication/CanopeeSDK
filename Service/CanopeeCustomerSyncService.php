<?php

namespace Akyos\CanopeeSDK\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Akyos\CanopeeModuleSDK\Service\ProviderService;

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

    public function createOrUpdateFromCanopee(?object $customerCanopee)
    {
        $entityClass = $this->container->getParameter('entity')['customer_entity'];
        if(!$customer = $this->entityManager->getRepository($entityClass)->findOneBy(['canopeeId' => $customerCanopee->id])){
            $customer = new ($entityClass)();
            $customer->setCanopeeId($customerCanopee->id);
        }
        $customer->setCustomerCanopee(json_encode($customerCanopee));

        $files = [
            'imageLogo',
            'imageMinifiedLogo',
        ];
        $entityName = explode("\\", $entityClass);
        foreach ($files as $file) {
            $this->canopeeFilePool->delete($user->getId().'_'.$file.'_'.end($entityName));
        }

        if($customerCanopee->deletedState === 'delete'){
            $this->entityManager->getRepository($entityClass)->remove($customer, true);
        }

        $this->entityManager->getRepository($entityClass)->add($customer, true);

        return $customer;
    }
}
