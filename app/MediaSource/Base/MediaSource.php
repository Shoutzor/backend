<?php

namespace App\MediaSource\Base;

abstract class MediaSource
{
    protected string $identifier;
    protected string $name;
    protected string $icon;
    protected array $agents;
    protected array $processors;

    public function __construct() {}

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getAgents(): array
    {
        return $this->agents;
    }

    public function getProcessors(): array
    {
        return $this->processors;
    }
}
