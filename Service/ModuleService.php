<?php

namespace Akyos\CanopeeSDK\Service;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;

class ModuleService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function getModuleLinks(UserInterface $user, bool $externalModules = false): array
    {
        $moduleLinks = [];

        $internalModules = $this->entityManager->getRepository('App\\Entity\\Module')->findBy(['external' => $externalModules]);

        foreach($internalModules as $module) {
            $moduleLinks[$module->getId()] = [
                'name' => $module->getName(),
                'url' => $module->getUrl(),
                'logo' => $module->getLogo(),
                'active' => false,
            ];
        }

        $userModules = $user->getModuleRoles()->map(function($moduleRole) {
            return $moduleRole->getModule();
        });
        $userModules = $userModules->filter( function($module) use($externalModules) {
            return $externalModules ? $module->isExternal() : !$module->isExternal();
        });
        foreach($userModules as $module) {
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
