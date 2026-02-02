<?php

namespace Akyos\CanopeeSDK\Twig;

use Akyos\CanopeeSDK\Service\ModuleService;
use Akyos\CanopeeModuleSDK\Service\ProviderService;
use Akyos\CanopeeModuleSDK\Class\Get;
use Akyos\CanopeeSDK\Service\UserAccessRightsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\TwigFunction;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

class CanopeeSDKExtension extends AbstractExtension implements GlobalsInterface
{

    public TagAwareAdapter $cache;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ContainerInterface    $container,
        private readonly RequestStack         $requestStack,
        private readonly KernelInterface      $kernel,
        private readonly ProviderService      $provider,
        private readonly UserAccessRightsService $userAccessRightsService,
    ) {
        $this->cache = new TagAwareAdapter(new FilesystemAdapter());
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('canopee_sdk_path', [$this, 'canopeeSDKPath']),
            new TwigFunction('getFile', [$this, 'getFile']),
            new TwigFunction('notifications', [$this, 'notifications']),
            new TwigFunction('currentUserAccessRight', [$this->userAccessRightsService, 'getLoggedUserAccessRight']),
            new TwigFunction('getCustomers', [$this->userAccessRightsService, 'getCustomers']),
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

        $name = 'image_' . $resource . $entity . $property;
        $repertoire = $this->kernel->getProjectDir().'/uploads/';
        $fileName = $repertoire. $name;

        $finder = new Finder();

        $files = $finder
            ->in($repertoire)
            ->files()
            ->date('since 1 hour ago')
            ->name($name . '.*');

        foreach ($files as $file) {
            $fileName = $file->getRealPath();
        }

        if(!$files->hasResults()){
            $query = (new Get())
                ->setResource('file/'.$resource)
                ->setPathParams(['id' => $entity])
                ->setQueryParams(['fieldName' => $property])
            ;
            $result = $this->provider->initialize('canopee')->send($query)->getData();
            if (is_object($result) && property_exists($result, 'file') && $result->file) {
                [$type, $data] = explode(';', $result->file);
                [, $data]      = explode(',', $data);
                $extension         = explode('/', $type)[1];

                $fileName = $fileName . '.' . $extension;

                $image_data = base64_decode($data);

                file_put_contents($fileName, $image_data);
            }
        }
        return $this->urlGenerator->generate('app.stream_document', ['file' => $fileName]);
    }

    public function notifications(): string
    {
        $query = (new Get())
            ->setResource('notifications')
        ;
        $result = $this->provider->initialize('canopee')->send($query)->getData();
        return json_encode($result);
    }

    public function getGlobals(): array
    {
        return [
            'userCanopee' => $this->requestStack->getSession()->get('userCanopee'),
        ];
    }
}
