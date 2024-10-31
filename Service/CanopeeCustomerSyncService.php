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

        if(!$customer = $customerRepository->findById($customerCanopee->id)->getQuery()->getOneOrNullResult()) {
            $customer = new ($customerEntityClass)();
            $customer->setId($customerCanopee->id);
            $customerRepository->add($customer, true);
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
            $existingCustomerAccessRight = $customerAccessRightRepository->findByCustomer($customer, null ,$customerAccessRight->accessCategory->name)->getQuery()->getOneOrNullResult();
            if(!$existingCustomerAccessRight) {
                $newCustomerAccessRight = (new ($customerAccessRightsEntityClass)())
                    ->setBeneficiaryCustomer($customer)
                    ->setAccessCategory($customerAccessRight->accessCategory->name)
                ;

                $customerAccessRightRepository->add($newCustomerAccessRight, true);
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
