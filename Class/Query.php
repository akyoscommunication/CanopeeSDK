<?php

namespace Akyos\CanopeeSDK\Class;

use Akyos\CanopeeSDK\Service\ProviderService;
use Symfony\Contracts\Service\Attribute\Required;

class Query
{
    private string $resource;
    private array $queryParams = [];
    private array $pathParams = [];
    private ?array $results = null;

    #[Symfony\Component\DependencyInjection\Autowired]
    private ProviderService $providerService;

    #[Required]
    public function setProviderService(ProviderService $providerService): void
    {
        $this->providerService = $providerService;
    }

    public function getResults(): array
    {
        if($this->results === null) {
            $request = $this->providerService->get($this);
            dd($request);
        }
        return $this->results;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function setResource(string $resource): self
    {
        $this->resource = $resource;
        return $this;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function setQueryParams(array $queryParams): self
    {
        $this->queryParams = $queryParams;
        return $this;
    }

    public function getPathParams(): array
    {
        return $this->pathParams;
    }

    public function setPathParams(array $pathParams): self
    {
        $this->pathParams = $pathParams;
        return $this;
    }
}