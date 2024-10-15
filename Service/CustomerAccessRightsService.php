<?php

namespace Akyos\CanopeeSDK\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

readonly class CustomerAccessRightsService
{
    public const CATEGORY_CUSTOMER_MODULE = 'customer-module';
    public const CATEGORY_CUSTOMER_PARTNER = 'customer-partner';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContainerInterface     $container,
    ) {
    }

    public function getCustomerAccessRights(mixed $beneficiaryCustomer = null, mixed $mandatingCustomer = null, ?string $accessCategory = null): array
    {
        $customerAccessRightClass = $this->container->getParameter('entity')['customer_access_right_entity'];
        $customerClass = $this->container->getParameter('entity')['customer_entity'];


        if($beneficiaryCustomer && !($beneficiaryCustomer instanceof $customerClass)) {
            return [];
        }

        if($mandatingCustomer && !($mandatingCustomer instanceof $customerClass)) {
            return [];
        }

        return $this->entityManager->getRepository($customerAccessRightClass)->findByCustomer($beneficiaryCustomer, $mandatingCustomer, $accessCategory)->getQuery()->getResult();
    }
}
