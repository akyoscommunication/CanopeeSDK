<?php

namespace Akyos\CanopeeSDK\Twig;

use Akyos\CanopeeSDK\Service\ModuleService;
use Akyos\CanopeeSDK\Service\ProviderService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CanopeeSDKExtension extends AbstractExtension
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ContainerInterface    $container,
        private readonly ProviderService       $providerService
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('canopee_sdk_path', [$this, 'canopeeSDKPath']),
            new TwigFunction('new', [$this->providerService, 'new']),
            new TwigFunction('getUserFile', [$this, 'getUserFile']),
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

    public function getUserFile(int $entity, string $property): string
    {
        return $this->providerService->new('file/users', 'GET')->setPathParams(['id' => $entity])->setQueryParams(['fieldName' => $property])->getResults()->file;
    }
}
