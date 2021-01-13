<?php

namespace MartenaSoft\Maker\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class EntityInfo
{
    private string $name;
    private bool $isDatabase;
    private string $sysAction;

    private ArrayCollection $entityField;

    public function __construct()
    {
        $this->entityField = new ArrayCollection();
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

    public function isDatabase(): bool
    {
        return $this->isDatabase;
    }

    public function setIsDatabase(bool $isDatabase): self
    {
        $this->isDatabase = $isDatabase;
        return $this;
    }

    public function getEntityField(): ArrayCollection
    {
        return $this->entityField;
    }

    public function setEntityField(ArrayCollection $entityField): self
    {
        $this->entityField = $entityField;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
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
