<?php

namespace MartenaSoft\Maker\Entity;

class EntityField
{
    private ?string $name = null;
    private ?int $size = null;
    private ?string $type = null;
    private ?string $templateName = null;
    private ?array $types = null;
    private ?array $nameTemplates = null;
    private bool $isForm;

    public function __construct()
    {
        $this->types = [
            'Integer' => 1,
            'String' => 2,
            'Text' => 3,
            'Date' => 4,
            'Boolean' => 5
        ];

        $this->nameTemplates = [
            'name' => 1,
            'date' => 2,
            'time' => 3,
        ];
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function getTypes(): ?array
    {
        return $this->types;
    }

    public function setTypes(?array $types): self
    {
        $this->types = $types;
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

    public function getTemplateName(): ?string
    {
        return $this->templateName;
    }

    public function setTemplateName(?string $templateName): self
    {
        $this->templateName = $templateName;
        return $this;
    }

    public function isForm(): bool
    {
        return $this->isForm;
    }

    public function setIsForm(bool $isForm): EntityField
    {
        $this->isForm = $isForm;
        return $this;
    }

    public function getNameTemplates(): ?array
    {
        return $this->nameTemplates;
    }

    public function setNameTemplates(?array $nameTemplates): self
    {
        $this->nameTemplates = $nameTemplates;
        return $this;
    }
}
