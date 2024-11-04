<?php

namespace Akyos\CanopeeSDK\Service;

use App\Entity\UserAccessRight;
use App\Repository\UserAccessRightRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class UserAccessRightsService
{
    public const CATEGORY_PUBLIC = 'public';
    public const CATEGORY_PARTNER = 'partner';
    public const CATEGORY_COLLABORATOR = 'collaborator';
    public const CATEGORY_SUPER_ADMIN = 'super-admin';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContainerInterface     $container,
        private RequestStack $requestStack,
        private UserAccessRightRepository $userAccessRightRepository,
        private TranslatorInterface $translator,
    ) {
    }

    public function getLoggedUserAccessRight(): mixed
    {
        $userAccessRightClass = $this->container->getParameter('entity')['user_access_right_entity'];
        $hasUserAccessRight = $this->requestStack->getSession()->has('userAccessRights');

        if($hasUserAccessRight) {
            $userAccessRightInSession = $this->requestStack->getSession()->get('userAccessRights');
            $userAccessRightId = is_object($userAccessRightInSession) ? $userAccessRightInSession->getId() : $userAccessRightInSession;
            return $this->entityManager->getRepository($userAccessRightClass)->findById($userAccessRightId)->getQuery()->getOneOrNullResult();
        }

        return null;
    }

    public function setLoggedUserAccessRight(mixed $userAccessRight = null): mixed
    {
        if($userAccessRight) {
            $this->requestStack->getSession()->set('userAccessRights', $userAccessRightId);
        } else {
            $this->requestStack->getSession()->remove('userAccessRights');
        }

        return $userAccessRight;
    }

    // Get user's userAccessRights
    public function getUserAccessRights(mixed $user = null, ?string $accessCategory = null, mixed $customer = null): array
    {
        $userClass = $this->container->getParameter('entity')['user_entity'];
        $userAccessRightClass = $this->container->getParameter('entity')['user_access_right_entity'];
        $customerClass = $this->container->getParameter('entity')['customer_entity'];

        if($user && !($user instanceof $userClass)) {
            return [];
        }

        if($customer && !($customer instanceof $customerClass)) {
            return [];
        }

        return $this->entityManager->getRepository($userAccessRightClass)->findByUser($user, $accessCategory, $customer)->getQuery()->getResult();
    }

    // Get all customers where the user has access rights for
    public function getCustomers(mixed $user): array
    {
        $userClass = $this->container->getParameter('entity')['user_entity'];

        if($user && !($user instanceof $userClass)) {
            return [];
        }

        return array_unique(array_map(function(mixed $userAccessRight) {
            return $userAccessRight->getCustomer();
        }, $this->getUserAccessRights($user, self::CATEGORY_COLLABORATOR)));
    }

    // Get users which have such user access rights
    public function getUsers(?string $accessCategory = null, mixed $customer = null): array
    {
        $customerClass = $this->container->getParameter('entity')['customer_entity'];

        if($customer && !($customer instanceof $customerClass)) {
            return [];
        }

        return array_unique(array_map(function(mixed $userAccessRight) {
            return $userAccessRight->getUser();
        }, $this->getUserAccessRights(null, $accessCategory, $customer)));
    }

    // Add userAcessRight to user
    public function addUserAccessRight(mixed $user, string $accessCategory, mixed $customer, ?bool $flush = true): mixed
    {
        $userClass = $this->container->getParameter('entity')['user_entity'];
        $userAccessRightClass = $this->container->getParameter('entity')['user_access_right_entity'];
        $customerClass = $this->container->getParameter('entity')['customer_entity'];

        if(!$user || !($user instanceof $userClass)) {
            return null;
        }

        if(!$customer || !($customer instanceof $customerClass)) {
            return null;
        }

        $existingUserAccessRights = $this->getUserAccessRights($user, $accessCategory, $customer);
        if(count($existingUserAccessRights)) {
            return $existingUserAccessRights[0];
        }

        $userAccessRight = (new $userAccessRightClass())
            ->setUser($user)
            ->setAccessCategory($accessCategory)
            ->setCustomer($customer)
        ;

        $this->entityManager->getRepository($userAccessRightClass)->add($userAccessRight, $flush);

        return $userAccessRight;
    }

    // Remove userAcessRight to user
    public function removeUserAccessRight(mixed $user, string $accessCategory, mixed $customer, ?bool $flush = true): bool
    {
        $userClass = $this->container->getParameter('entity')['user_entity'];
        $userAccessRightClass = $this->container->getParameter('entity')['user_access_right_entity'];
        $customerClass = $this->container->getParameter('entity')['customer_entity'];

        if(!$user || !($user instanceof $userClass)) {
            return false;
        }

        if(!$customer || !($customer instanceof $customerClass)) {
            return false;
        }

        $existingUserAccessRights = $this->getUserAccessRights($user, $accessCategory, $customer);

        foreach($existingUserAccessRights as $existingUserAccessRight) {
            $this->entityManager->getRepository($userAccessRightClass)->remove($existingUserAccessRight, $flush);
        }

        return true;
    }

    // String representation of the user's access rights
    public function toString(UserAccessRight $userAccessRight): string
    {
        $toString = '';

        switch ($userAccessRight->getAccessCategory()) {
            case $this::CATEGORY_PARTNER:
            case $this::CATEGORY_PUBLIC:
            case $this::CATEGORY_SUPER_ADMIN:
                $toString = $this->translator->trans($userAccessRight->getAccessCategory(), [], 'user.access_type');
                break;
            case $this::CATEGORY_COLLABORATOR:
                $toString = $this->translator->trans($userAccessRight->getAccessCategory(), [], 'user.access_type') . ' ' . $userAccessRight->getCustomer()?->getLegalName();
                break;
        }

        return $toString;
    }
}
