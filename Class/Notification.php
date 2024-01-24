<?php

namespace Akyos\CanopeeSDK\Class;

use Akyos\CanopeeModuleSDK\Class\Post;
use App\Enum\Notification\Types;
use Akyos\CanopeeModuleSDK\Translation\CanopeeTranslatableMessage;
use Akyos\CanopeeModuleSDK\Class\AbstractQueryObject;

class Notification extends AbstractQueryObject
{
    public string $module;
    public int $about;
    /** @TODO: a typer avec les Types de canopée ? */
    public string $type;
    public string $name;
    public string $description;
    public string $target;

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
        return unserialize($this->name, ['allowed_classes' => [CanopeeTranslatableMessage::class]]);
    }

    public function setName(CanopeeTranslatableMessage $name): static
    {
        $this->name = serialize($name);

        return $this;
    }

    public function getDescription(): CanopeeTranslatableMessage
    {
        return unserialize($this->description, ['allowed_classes' => [CanopeeTranslatableMessage::class]]);
    }

    public function setDescription(CanopeeTranslatableMessage $description): static
    {
        $this->description = serialize($description);

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
}
