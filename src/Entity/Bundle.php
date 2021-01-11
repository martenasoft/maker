<?php

namespace MartenaSoft\Maker\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use MartenaSoft\Maker\DependencyInjection\Configuration;

class Bundle
{
    private string $name;
    private string $rootDir;
    private string $path;
    private bool $isFrontend;
    private bool $isAdmin;
    private string $sysAction = '';
    private array $arrayCollections = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    public function setRootDir(string $rootDir): self
    {
        $this->rootDir = $rootDir;
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

    public function isFrontend(): bool
    {
        return $this->isFrontend;
    }

    public function setIsFrontend(bool $isFrontend): self
    {
        $this->isFrontend = $isFrontend;
        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;
        return $this;
    }

    public function getCollection(string $name): ArrayCollection
    {
        if (!isset($this->arrayCollections[$name])) {
            $this->arrayCollections[$name] = new ArrayCollection();
        }

        return $this->arrayCollections[$name];
    }

    public function __get(string $name)
    {
        if (!in_array($name, Configuration::getDirectories())) {
            return null;
        }

        if (!isset($this->arrayCollections[$name])) {
            $this->arrayCollections[$name] = new ArrayCollection();
        }

        return $this->arrayCollections[$name];
    }

    public function getArrayCollections(): array
    {
        return $this->arrayCollections;
    }

    public function getSysAction(): string
    {
        return $this->sysAction;
    }

    public function setSysAction(string $sysAction): self
    {
        $this->sysAction = $sysAction;
        return $this;
    }
}
