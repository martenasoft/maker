<?php

namespace MartenaSoft\Maker\Service;

use MartenaSoft\Maker\Entity\EntityInfo;

class FormService
{
    private TemplateFileService $templateFileService;
    private BundleService $bundleService;

    public function __construct(TemplateFileService $templateFileService, BundleService $bundleService)
    {
        $this->templateFileService = $templateFileService;
        $this->bundleService = $bundleService;
    }

    public function collectData(EntityInfo $entityInfo): string
    {
        $result = '';

        $this->templateServiceInit($entityInfo);

        $bundleData = $this->bundleService->getBundle($entityInfo->getBundleName());
        $content = $this->templateFileService->getTemplateContent(' ');
        $typesFile = $this->templateFileService->getTemplatePath().'/Form/types.php';

        $types = require $typesFile;

        $content_ = $this->templateFileService->getPlacesToInsertVariablesAndFunctions($content);

        return $result;
    }
}
