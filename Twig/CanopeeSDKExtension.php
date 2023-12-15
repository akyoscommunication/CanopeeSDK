<?php

namespace Akyos\CanopeeSDK\Twig;

use Akyos\CanopeeSDK\Service\ModuleService;
use Akyos\CanopeeSDK\Service\ProviderService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

class CanopeeSDKExtension extends AbstractExtension implements GlobalsInterface
{

    public TagAwareAdapter $cache;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ContainerInterface    $container,
        private readonly ProviderService       $providerService,
        private readonly RequestStack         $requestStack
    ) {
        $this->cache = new TagAwareAdapter(new FilesystemAdapter());
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('canopee_sdk_path', [$this, 'canopeeSDKPath']),
            new TwigFunction('new', [$this->providerService, 'new']),
            new TwigFunction('getFile', [$this, 'getFile']),
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

    public function getFile(string $resource, int $entity, string $property): mixed
    {
        return $this->cache->get($resource.$entity.$property, function (mixed $item) use ($resource, $entity, $property): mixed {
            $result = $this->providerService->new('file/'.$resource, 'GET')->setPathParams(['id' => $entity])->setQueryParams(['fieldName' => $property])->getResults();
            if (is_object($result) && property_exists($result, 'file')) {
                $item->tag('file');

                return $result->file;
            }

            return null;
        });
    }

    public function getGlobals(): array
    {
        return [
            'userCanopee' => $this->requestStack->getSession()->get('userCanopee'),
        ];
    }
}
