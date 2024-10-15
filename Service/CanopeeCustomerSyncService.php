<?php

namespace Akyos\CanopeeSDK\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Akyos\CanopeeModuleSDK\Service\ProviderService;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

readonly class CanopeeCustomerSyncService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContainerInterface     $container,
        private TagAwareCacheInterface $canopeeFilePool,
    )
    {
    }

    public function createOrUpdateFromCanopee(?object $customerCanopee)
    {
        $customerEntityClass = $this->container->getParameter('entity')['customer_entity'];
        if(!$customerEntityClass) {
            $customerEntityClass = 'App\Entity\Customer';
        }
        $customerRepository = $this->entityManager->getRepository($customerEntityClass);

        if(!$customer = $customerRepository->findOneBy(['canopeeId' => $customerCanopee->id])){
            $customer = new ($customerEntityClass)();
            $customer->setCanopeeId($customerCanopee->id);
        }

        $customerAccessRights = array_filter((array) $customerCanopee->customerAccessRights, function($customerAccessRight) {
            return $customerAccessRight->module->slug === $this->container->getParameter('module_slug');
        });

        $customerAccessRightsEntityClass = $this->container->getParameter('entity')['customer_access_right_entity'];
        if(!$customerAccessRightsEntityClass) {
            $customerAccessRightsEntityClass = 'App\Entity\CustomerAccessRight';
        }
        $customerAccessRightRepository = $this->entityManager->getRepository($customerAccessRightsEntityClass);

        foreach($customerAccessRights as $customerAccessRight) {
            $existingCustomerAccessRight = $customerAccessRightRepository->findOneBy([
                'accessRight' => $customerAccessRight->accessCategory->name,
                'customer' => $customer,
            ]);
            if(!$existingCustomerAccessRight) {
                $newCustomerAccessRight = (new ($customerAccessRightsEntityClass)())
                    ->setCustomer($customer)
                    ->setAccessRight($customerAccessRight->accessCategory->name)
                ;

                $customerAccessRightRepository->add($newCustomerAccessRight, false);
            }
        }

        $customer->setCustomerCanopee(json_encode($customerCanopee));

        $files = [
            'imageLogo',
            'imageMinifiedLogo',
        ];
        $entityName = explode("\\", $customerEntityClass);
        foreach ($files as $file) {
            $this->canopeeFilePool->delete($customer->getId().'_'.$file.'_'.end($entityName));
        }

        if($customerCanopee->deletedState === 'delete'){
            $customerRepository->remove($customer, true);
        }

        $customerRepository->add($customer, true);

        return $customer;
    }
}
