<?php

namespace MartenaSoft\Maker\Entity;

class BundleElementsEntity
{
    public const REPLACE_CONTENT = 1;
    public const LEAVE_OLD_CONTENT = 2;
    public const APPEND_CONTENT = 3;

    private string $name;
    private string $path;
    private string $content = '';
    private string $existsContent = '';
    private int $existsContentAction = 1;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self 
    {
        $this->content = $content;
        return $this;
    }

    public function getExistsContent(): string
    {
        return $this->existsContent;
    }

    public function setExistsContent(string $existsContent): void
    {
        $this->existsContent = $existsContent;
    }

    public function getExistsContentAction(): int
    {
        return $this->existsContentAction;
    }

    public function setExistsContentAction(int $existsContentAction): void
    {
        $this->existsContentAction = $existsContentAction;
    }
}
