<?php

namespace MartenaSoft\Maker\Entity;

class ClassEntity
{
    private string $name;
    private string $content = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): ClassEntity
    {
        $this->content = $content;
        return $this;
    }
}
