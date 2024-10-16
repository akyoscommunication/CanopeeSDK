<?php

namespace Akyos\CanopeeSDK\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Akyos\CanopeeModuleSDK\Service\ProviderService;
use Akyos\CanopeeModuleSDK\Class\Get;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

readonly class CanopeeUserSyncService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContainerInterface     $container,
        private RequestStack           $requestStack,
        private ProviderService        $provider,
        private Security               $security,
        private TagAwareCacheInterface $canopeeFilePool,
    )
    {
    }

    public function createOrUpdateFromCanopee(?object $userCanopee)
    {
        dump($userCanopee);
        $entityClass = $this->container->getParameter('entity')['user_entity'];
        if(!$entityClass) {
            $entityClass = 'App\Entity\User';
        }
        $userRepository = $this->entityManager->getRepository($entityClass);

        if(!$user = $userRepository->findOneBy(['uuid' => $userCanopee->id])) {
            $user = new ($entityClass)();
            $user->setUuid($userCanopee->id);
        }

        $userAccessRights = [];
        if(property_exists($userCanopee, 'userAccessRights')) {
            $userAccessRights = array_filter((array) $userCanopee->userAccessRights, function($userAccessRight) {
                return $userAccessRight->module->slug === $this->container->getParameter('module_slug');
            });
        }

        $userAccessRightsEntityClass = $this->container->getParameter('entity')['user_access_right_entity'];
        if(!$userAccessRightsEntityClass) {
            $entityClass = 'App\Entity\UserAccessRight';
        }
        $userAccessRightRepository = $this->entityManager->getRepository($userAccessRightsEntityClass);

        $customerEntityClass = $this->container->getParameter('entity')['customer_entity'];
        if(!$customerEntityClass) {
            $customerEntityClass = 'App\Entity\Customer';
        }
        $customerRepository = $this->entityManager->getRepository($customerEntityClass);

        if(!count($userAccessRights)) {
            $existingUserAccessRight = $userAccessRightRepository->findBy([
                'user' => $user,
            ]);
            foreach ($existingUserAccessRight as $userAccessRight) {
                $userAccessRightRepository->remove($userAccessRight, false);
            }
        }
        foreach($userAccessRights as $userAccessRight) {
            $existingUserAccessRight = $userAccessRightRepository->findOneBy([
                'user' => $user,
                'accessCategory' => $userAccessRight->accessCategory->name,
                'customer' => $userAccessRight->customer->id,
            ]);
            if(!$existingUserAccessRight) {
                $newUserAccessRight = (new ($userAccessRightsEntityClass)())
                    ->setUser($user)
                    ->setActive($userAccessRight->active)
                    ->setRoles(array_map(fn($role) => $role->value, $userAccessRight->roles))
                    ->setAccessCategory($userAccessRight->accessCategory->name)
                    ->setDeletedState($userAccessRight->deletedState)
                ;

                if(property_exists($userAccessRight, 'customer') && $userAccessRight->customer !== null) {
                    $newUserAccessRight->setCustomer($customerRepository->findOneBy(['canopeeId' => $userAccessRight->customer->id]));
                }

                $userAccessRightRepository->add($newUserAccessRight, false);
            } else {
                $existingUserAccessRight
                    ->setRoles(array_map(fn($role) => $role->value, $userAccessRight->roles))
                    ->setActive($userAccessRight->active)
                    ->setDeletedState($userAccessRight->deletedState)
                ;
                $userAccessRightRepository->add($existingUserAccessRight, false);
            }
        }

        if(in_array('ROLE_SUPER_ADMIN', $userCanopee->roles)) {
            $user->setRoles(['ROLE_SUPER_ADMIN']);
        }
        else {
            $user->setRoles(['ROLE_USER']);
        }

        $user->setModuleToken($userCanopee->moduleToken);
        $user->setUserCanopee(json_encode($userCanopee));

        // clear cache of files
        $files = [
            'avatarFile',
        ];
        $entityName = explode("\\", $entityClass);
        foreach ($files as $file) {
            $this->canopeeFilePool->delete($user->getId().'_'.$file.'_'.end($entityName));
        }

        if($userCanopee->deletedState === 'delete') {
            $userRepository->remove($user, true);
        }
        $userRepository->add($user, true);

        return $user;
    }

    public function sync(){
        $user = $this->security->getUser();
        $session = $this->requestStack->getSession();
        if($session->get('userCanopee') !== null){
            return null;
        }
        $query = (new Get())
            ->setResource('users')
            ->setPathParams(['uuid' => $user->getUuid()])
        ;
        $userCanopee = $this->provider->initialize('canopee')->send($query)->getData();
        $session->set('userCanopee', $userCanopee);
    }
}
