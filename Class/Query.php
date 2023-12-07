<?php

namespace Akyos\CanopeeSDK\Class;

use Akyos\CanopeeSDK\Service\ProviderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Service\Attribute\Required;

class Query
{
    private ?string $resource = null;

    private ?string $method = null;

    private ?int $items = null;
    private ?int $page = 1;

    private array $queryParams = [];
    private array $pathParams = [];

    private array $body = [];
    private mixed $results = null;

    private ProviderService $providerService;

    public function __construct(ProviderService $providerService, string $method = '' , ?string $resource = null)
    {
        $this->providerService = $providerService;
        $this->resource = $resource;
        $this->method = $method;
    }

    public function getResults(): array|\stdClass|null
    {
        if($this->results === null) {
            $request = $this->providerService->get($this);
            if(property_exists($request, 'hydra:member')){
                $this->results = $request->{'hydra:member'};
                $this->items = $request->{'hydra:totalItems'};
            }else{
                $this->results = $request;
            }
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
        return array_merge(['page' => $this->page], $this->queryParams);
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

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function getItems(): ?int
    {
        return $this->items;
    }

    public function setItems(?int $items): self
    {
        $this->items = $items;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(?string $context): self
    {
        $this->context = $context;
        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;
        return $this;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function setBody(array $body): void
    {
        $this->body = $body;
    }
}
