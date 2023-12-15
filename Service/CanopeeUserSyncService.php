<?php

namespace Akyos\CanopeeSDK\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Akyos\CanopeeSDK\Service\ProviderService;


class CanopeeUserSyncService
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

    public function createOrUpdateFormCanopee(?object $userCanopee){

        if(!$user = $this->entityManager->getRepository($this->container->getParameter('entity')['user_entity'])->findOneBy(['uuid' => $userCanopee->id])){
            $user = new ($this->container->getParameter('entity')['user_entity'])();
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

        if($userCanopee->customer !== null){
            $user->setCustomer($this->entityManager->getRepository($this->container->getParameter('entity')['customer_entity'])->findOneBy(['canopeeId' => $userCanopee->customer->id]));
        }

        if($userCanopee->deletedState === 'delete'){
            $this->entityManager->getRepository($this->container->getParameter('entity')['user_entity'])->remove($user, true);
        }
        $this->entityManager->getRepository($this->container->getParameter('entity')['user_entity'])->add($user, true);

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
