<?php

namespace Akyos\CanopeeSDK\Webhook;

use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\HostRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Webhook\Client\AbstractRequestParser;

final class SyncCustomerParser extends AbstractRequestParser
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    protected function getRequestMatcher(): RequestMatcherInterface
    {
        $parsed_url = parse_url($this->container->getParameter('api')['endpoint']);
        $host = $parsed_url['host'];
        return new ChainRequestMatcher([
            new HostRequestMatcher($host),
            new MethodRequestMatcher('POST'),
            new IsJsonRequestMatcher(),
        ]);
    }

    protected function doParse(Request $request, string $secret): RemoteEvent
    {
        $content = $request->toArray();
        return new RemoteEvent('sync_customer', 'sync_customer_'.$content['id'], $content);
    }
}
