<?php

namespace Akyos\CanopeeSDK\Class;

use Akyos\CanopeeModuleSDK\Class\Post;
use App\Enum\Notification\Types;
use Akyos\CanopeeModuleSDK\Translation\CanopeeTranslatableMessage;
use Akyos\CanopeeModuleSDK\Class\AbstractQueryObject;
use Psr\Container\ContainerInterface;

class Notification extends AbstractQueryObject implements NotificationInterface
{
    public string $module;
    public int $about;
    /** @TODO: a typer avec les Types de canopée ? */
    public string $type;
    public mixed $name;
    public mixed $description;
    public string $target;
    public ?int $user = null; 

    public function __construct()
    {
        $this->resource = 'notifications';
    }

    // generated getters and setters
    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): static
    {
        $this->module = $module;

        return $this;
    }

    public function getAbout(): int
    {
        return $this->about;
    }

    public function setAbout(int $about): static
    {
        $this->about = $about;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): CanopeeTranslatableMessage
    {
        return $this->name;
    }

    public function setName(CanopeeTranslatableMessage $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): CanopeeTranslatableMessage
    {
        return $this->description;
    }

    public function setDescription(CanopeeTranslatableMessage $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getUser(): ?int
    {
        return $this->user;
    }

    public function setUser(int $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function dataTransform(ContainerInterface $container): array
    {
        $translator = $container->get('translator');

        $this->name = (object)$this->name->setTranslatedMessage(
            $this->name->trans($translator)
        )->toArray();

        $this->description = (object)$this->description->setTranslatedMessage(
            $this->description->trans($translator)
        )->toArray();

        return [
            'module' => $this->module,
            'about' => $this->about,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'target' => $this->target,
            'user' => $this->user,
        ];
    }
}
