<?php

namespace Akyos\PuppeteerSDK\Twig;

use Akyos\CanopeeSDK\Service\ModuleService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

readonly class CanopeeSDKExtension extends AbstractExtension
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ContainerInterface    $container,
        private ModuleService         $moduleService,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('canopee_sdk_path', [$this, 'canopeeSDKPath']),
            new TwigFunction('getInternalModuleLinks', [$this->moduleService, 'getInternalModuleLinks']),
        ];
    }

    public function canopeeSDKPath($action, $filename, $name, $parameters = [])
    {
        return $this->urlGenerator->generate("canopee_$action", [
            'token' => $this->container->getParameter('token')['key'],
            'filename' => $filename,
            'url' => $this->urlGenerator->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }
}
