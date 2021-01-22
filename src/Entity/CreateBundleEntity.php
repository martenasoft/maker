<?php

namespace MartenaSoft\Maker\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use MartenaSoft\Maker\DependencyInjection\Configuration;

class CreateBundleEntity
{
    private string $path;
    private string $namespace;
    private string $name;
    private string $description;
    private string $gitUrl;
    private bool $isInitGitRepository = true;
    private bool $isInitComposerJson = true;
    private array $modules = [];
    private ArrayCollection $data;

    public function __construct()
    {
        $this->modules = Configuration::getDirectories();
        $this->data = new ArrayCollection();
    }

    public function getChoiceValue()
    {

        return array_combine(
            $this->modules,
            array_fill(0, count($this->modules), true)
        );
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): CreateBundleEntity
    {
        $this->path = $path;
        return $this;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getGitUrl(): string
    {
        return $this->gitUrl;
    }

    public function setGitUrl(string $gitUrl): self
    {
        $this->gitUrl = $gitUrl;
        return $this;
    }

    public function isInitGitRepository(): bool
    {
        return $this->isInitGitRepository;
    }

    public function setIsInitGitRepository(bool $isInitGitRepository): self
    {
        $this->isInitGitRepository = $isInitGitRepository;
        return $this;
    }

    public function isInitComposerJson(): bool
    {
        return $this->isInitComposerJson;
    }

    public function setIsInitComposerJson(bool $isInitComposerJson): self
    {
        $this->isInitComposerJson = $isInitComposerJson;
        return $this;
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    public function setModules(array $modules): self
    {
        $this->modules = $modules;
        return $this;
    }

    public function getData(): ArrayCollection
    {
        return $this->data;
    }

    public function setData(ArrayCollection $data): void
    {
        $this->data = $data;
    }
}
