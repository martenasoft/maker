<?php

namespace MartenaSoft\Maker\Service;

use MartenaSoft\Maker\Entity\EntityInfo;
use MartenaSoft\Maker\Service\BundleService;
use MartenaSoft\Maker\Service\TemplateFileService;
use MartenaSoft\Maker\Service\EmbedCodeService;



class FormService
{
    private TemplateFileService $templateFileService;
    private BundleService $bundleService;
    private EmbedCodeService $codeService;

    public function __construct(
        TemplateFileService $templateFileService,
        BundleService $bundleService,
        EmbedCodeService $codeService
    ) {
        $this->templateFileService = $templateFileService;
        $this->bundleService = $bundleService;
        $this->codeService = $codeService;
    }

    public function collectData(EntityInfo $entityInfo): string
    {
        $result = '';

        $this->templateServiceInit($entityInfo);

        $bundleData = $this->bundleService->getBundle($entityInfo->getBundleName());
        $content = $this->templateFileService->getTemplateContent('Form');
        $typesFile = $this->templateFileService->getTemplatePath().'/Form/types.php';

        $types = require $typesFile;

      //  $content_ = $this->templateFileService->getPlacesToInsertVariablesAndFunctions($content);
        $content_ = $this->codeService
            ->setContent($content)
            ->findMethod('buildForm')
            ->getResult();
        dump($content_); die;
        return $result;
    }

    private function templateServiceInit(EntityInfo $entityInfo): void
    {
        $this
            ->templateFileService
            ->addReplace(TemplateFileService::REPLACE_NAMESPACE, $entityInfo->getNamespace())
           // ->addReplace(TemplateFileService::REPLACE_BUNDLE_NAME, $entityInfo->getBundleName())
            ->addReplace(TemplateFileService::REPLACE_CLASS_PREFIX_NAME, $entityInfo->getName())
            ->addReplace('//', '/')
            ->addReplace('\\\\', '\\')
        ;
    }
}
