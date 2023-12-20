<?php

namespace Akyos\CanopeeSDK\Twig;

use Akyos\CanopeeSDK\Service\ModuleService;
use Akyos\CanopeeSDK\Service\ProviderService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Symfony\Component\HttpKernel\KernelInterface;
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
        private readonly RequestStack         $requestStack,
        private readonly KernelInterface      $kernel,
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

        $result = $this->providerService->new('file/'.$resource, 'GET')->setPathParams(['id' => $entity])->setQueryParams(['fieldName' => $property])->getResults();
        if (is_object($result) && property_exists($result, 'file') && $result->file) {
            list($type, $data) = explode(';', $result->file);
            list(, $data)      = explode(',', $data);
            $extension         = explode('/', $type)[1];

            $filename = $this->kernel->getProjectDir().'/uploads/image_' . $resource . $entity . $property . '.' . $extension;

            $image_data = base64_decode($data);

            file_put_contents($filename, $image_data);
            return $this->urlGenerator->generate('app.stream_document', ['file' => $this->kernel->getProjectDir().'/uploads/image_' . $resource . $entity . $property . '.' . $extension]);
        }
        return null;
    }

    public function getGlobals(): array
    {
        return [
            'userCanopee' => $this->requestStack->getSession()->get('userCanopee'),
        ];
    }
}
