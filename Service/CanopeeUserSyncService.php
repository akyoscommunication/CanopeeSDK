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

    public function createFormCanopee(?object $userCanopee){

        if(!$user = $this->entityManager->getRepository($this->container->getParameter('entity')['user_entity'])->findOneBy(['uuid' => $userCanopee->id])){
            $user = new ($this->entityManager->getRepository($this->container->getParameter('entity')['user_entity']))();
            $user->setUuid($userCanopee->id);
        }
        $moduleRoles = array_map(function($role){
            return $role->value;
        }, $userCanopee->moduleRoles);
        if(in_array('ROLE_SUPER_ADMIN', $userCanopee->roles)){
            $user->setRoles(['ROLE_SUPER_ADMIN']);
        }
        else if(in_array('ROLE_ADMIN', $userCanopee->roles)){
            $user->setRoles(['ROLE_ADMIN']);
        } else {
            $user->setRoles($roles);
        }
        $user->setModuleToken($userCanopee->moduleToken);
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
