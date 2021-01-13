<?php

namespace MartenaSoft\Maker\Service;

use MartenaSoft\Maker\Entity\EntityInfo;

class EntityService
{
    private TemplateFileService $fileService;

    public function __construct(TemplateFileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function collectData(EntityInfo $entityInfo): void
    {
        dump($entityInfo);
    }
}
