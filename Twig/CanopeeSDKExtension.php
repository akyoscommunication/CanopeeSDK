<?php

namespace Akyos\PuppeteerSDK\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CanopeeSDKExtension extends AbstractExtension
{
    private $urlGenerator;
    private $container;

    public function __construct(UrlGeneratorInterface $urlGenerator, ContainerInterface $container)
    {
        $this->urlGenerator = $urlGenerator;
        $this->container = $container;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('canopee_sdk_path', [$this, 'canopeeSDKPath']),
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
