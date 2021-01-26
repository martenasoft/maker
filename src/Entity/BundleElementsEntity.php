<?php

namespace MartenaSoft\Maker\Entity;

class BundleElementsEntity
{
    public const REPLACE_CONTENT = 1;
    public const LEAVE_OLD_CONTENT = 2;
    public const APPEND_CONTENT = 3;

    private string $name = '';
    private string $path = '';
    private string $content = '';
    private string $existsContent = '';
    private ?int $existsContentAction = null;
    private bool $isDirectory = true;
    private bool $isNeedCreate = true;

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

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
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

    public function setExistsContent(string $existsContent): self
    {
        $this->existsContent = $existsContent;
        return $this;
    }

    public function getExistsContentAction(): ?int
    {
        return $this->existsContentAction;
    }

    public function setExistsContentAction(?int $existsContentAction = null): self
    {
        $this->existsContentAction = $existsContentAction;
        return $this;
    }

    public function isDirectory(): bool
    {
        return $this->isDirectory;
    }
    public function setIsDirectory(bool $isDirectory): self
    {
        $this->isDirectory = $isDirectory;
        return $this;
    }

    public function isNeedCreate(): bool
    {
        return $this->isNeedCreate;
    }

    public function setIsNeedCreate(bool $isNeedCreate): self
    {
        $this->isNeedCreate = $isNeedCreate;
        return $this;
    }
}
