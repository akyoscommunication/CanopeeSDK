<?php

namespace Akyos\CanopeeSDK\Service;

use Symfony\Component\Security\Core\User\UserInterface;

class ModuleService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function getInternalModuleLinks(UserInterface $user)
    {
        $moduleLinks = [];
        $internalModules = $this->entityManager->getRepository(Module::class)->findBy(['external' => false]);
        foreach($internalModules as $module) {
            $moduleLinks[$module->getId()] = [
                'name' => $module->getName(),
                'url' => $module->getUrl(),
                'logo' => $module->getLogo(),
                'active' => false,
            ];
        }

        $userModules = $user->getModuleRoles()->map(function(ModuleRole $moduleRole) {
            return $moduleRole->getModule();
        });
        $userExternalModules = array_filter($userModules, function(Module $module) {
            return !$module->isExternal();
        });
        foreach($userExternalModules as $module) {
            $moduleLinks[$module->getId()] = [
                'name' => $module->getName(),
                'url' => $module->getUrl(),
                'logo' => $module->getLogo(),
                'active' => true,
            ];
        }

        return $moduleLinks;
    }
}
