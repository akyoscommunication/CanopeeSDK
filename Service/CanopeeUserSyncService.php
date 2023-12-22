<?php

namespace Akyos\CanopeeSDK\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Akyos\CanopeeSDK\Service\ProviderService;
use Symfony\Contracts\Cache\TagAwareCacheInterface;


class CanopeeUserSyncService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContainerInterface $container,
        private readonly RequestStack $requestStack,
        private readonly ProviderService $providerService,
        private readonly Security $security,
        private readonly TagAwareCacheInterface $canopeeFilePool,
    )
    {
    }

    public function createOrUpdateFromCanopee(?object $userCanopee)
    {
        $entityClass = $this->container->getParameter('entity')['user_entity'];
        if(!$user = $this->entityManager->getRepository($entityClass)->findOneBy(['uuid' => $userCanopee->id])){
            $user = new ($entityClass)();
            $user->setUuid($userCanopee->id);
        }

        $moduleRoles = array_filter((array) $userCanopee->moduleRoles, function($role){
            return $role->module->slug === $this->container->getParameter('module_slug');
        });

        $moduleRoles = array_map(function($role) use ($userCanopee){
            return $role->value;
        }, $moduleRoles);

        if(in_array('ROLE_SUPER_ADMIN', $userCanopee->roles)){
            $user->setRoles(['ROLE_SUPER_ADMIN']);
        }
        else if(in_array('ROLE_ADMIN', $userCanopee->roles)){
            $user->setRoles(['ROLE_ADMIN']);
        } else {
            $user->setRoles($moduleRoles);
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

        if(property_exists($userCanopee, 'customer') && $userCanopee->customer !== null){
            $user->setCustomer($this->entityManager->getRepository($this->container->getParameter('entity')['customer_entity'])->findOneBy(['canopeeId' => $userCanopee->customer->id]));
        }

        if($userCanopee->deletedState === 'delete'){
            $this->entityManager->getRepository($entityClass)->remove($user, true);
        }
        $this->entityManager->getRepository($entityClass)->add($user, true);

        return $user;
    }

    public function sync(){
        $user = $this->security->getUser();
        $session = $this->requestStack->getSession();
        if($session->get('userCanopee') !== null){
            return null;
        }
        $userCanopee = $this->providerService->new('users')->setPathParams(['uuid' => $user->getUuid()])->getResults();
        $session->set('userCanopee', $userCanopee);
    }
}
