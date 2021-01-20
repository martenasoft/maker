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
    private array $formTemplateArray = [];

    public function __construct(
        TemplateFileService $templateFileService,
        BundleService $bundleService,
        EmbedCodeService $codeService
    )
    {
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
        $typesFile = $this->templateFileService->getTemplatePath() . '/Form/types.php';

        $types = require $typesFile;

        //  $content_ = $this->templateFileService->getPlacesToInsertVariablesAndFunctions($content);
        $methodBuildForm = $this->codeService
            ->setContent($content)
            ->findMethod('buildForm');

        $entityFields = $entityInfo->getEntityField();
        $formTemplate = [];

        if (!empty($entityFields) && isset($methodBuildForm['methods']['buildForm']['body']['lines'])) {

            $use = $this->codeService->findUse();
            $insertedUse = [];
            $findSameFunctionVars = [];
            $lineBuilderVariable = 0;
            $addFunctionSemicolonLine = 0;


            foreach ($entityFields as $entityField) {
                if ($entityField->isForm()) {

                    $newField = "\n        ->add('{$entityField->getName()}')";
                    if (!empty($types[$entityField->getType()])) {
                        $newFieldType = $types[$entityField->getType()];
                        if (!empty($types[$entityField->getType()]['addMethod'])) {
                            $newField = str_replace(
                                [
                                    '__REPLACE_VAR_NAME__'
                                ],
                                [
                                    $entityField->getName()
                                ],
                                $types[$entityField->getType()]['addMethod']
                            );
                        }

                        if (!empty($types[$entityField->getType()]['namespace'])) {
                            $useLine = 0;
                            if (!empty($use['use'])) {
                                $useLine = $use['use'][count($use['use']) - 1]['line'];
                                foreach ($use['use'] as $useItem) {
                                    $insertedUse[] = preg_replace(['/^use\s+/', '/;/'], ['', ''], $useItem['body']);
                                }
                            }

                            if ($useLine == 0 && isset($use['namespace']['line'])) {
                                $useLine = $use['namespace']['line'];
                            }

                            $namespace = preg_replace(
                                ['/^use\s+/', '/;/'],
                                ['', ''],
                                $types[$entityField->getType()]['namespace']
                            );

                            if (!in_array($namespace, $insertedUse)) {
                                $insertedUse[] = $namespace;
                                $this->codeService->set(
                                    'use ' . $types[$entityField->getType()]['namespace'] . ";",
                                    $useLine,
                                    true
                                );
                            }
                        }

                        if (!empty($types[$entityField->getType()]['template'])) {
                            $formTemplate[] = str_replace(
                                ['__REPLACE_VAR_NAME__'],
                                [$entityField->getName()],
                                $types[$entityField->getType()]['template']
                            );
                        }
                    }


                    foreach ($methodBuildForm['methods']['buildForm']['body']['lines'] as $line) {
                        if (strpos($line['body'], '$builder') !== false) {
                            $lineBuilderVariable = $line['line'];
                        }

                        if ($lineBuilderVariable > 0 && strpos($line['body'], ';') != false) {
                            $addFunctionSemicolonLine = $line['line'];
                        }

                        $pattern = "/add\((\'|\\\")([a-zA-Z0-9_]+)(\'|\\\")/";
                        if (preg_match($pattern, $line['body'], $matches) &&
                            !empty($matches[2]) &&
                            $matches[2] == $entityField->getName()
                        ) {
                            $findSameFunctionVars[] = $entityField->getName();
                        }
                    }

                    if (in_array($entityField->getName(), $findSameFunctionVars)) {
                        continue;
                    }

                    if ($lineBuilderVariable == 0) {
                        $lineBuilderVariable = $methodBuildForm['methods']['buildForm']['body']['lines'][0]['line'] + 1;
                        $this->codeService->set('       $builder', $lineBuilderVariable , true);

                    }

                    if ($addFunctionSemicolonLine == 0) {
                        $addFunctionSemicolonLine = $lineBuilderVariable + 1;

                        $newField .= ";";
                    }

                    $lineBuilderVariable++;
                    $this->codeService->set($newField, $addFunctionSemicolonLine, true);
                }
            }
        }

        $this->formTemplateArray = $formTemplate;
        return $this->codeService->getResult();
    }


    public function getTemplate(?string $content = null, string $templateFileName = 'template_default.txt'): ?string
    {
        $content = $this->templateFileService->getTemplateContent('Form', $templateFileName);
        if (!empty($content) && !empty($contentArray = explode("\n", $content))) {
            $insertLine = 0;

            foreach ($contentArray as $line => $body) {
                if (strpos($body, 'form_start')) {
                    $insertLine = $line + 1;
                    break;
                }
            }

            if ($insertLine == 0) {
                foreach ($contentArray as $line => $body) {
                    if (empty($body)) {
                        array_unshift($this->formTemplateArray, '   {{form_start(form)}}');
                        $insertLine = $line + 1;
                        break;
                    }
                }
            }

            array_splice(
                $contentArray,
                $insertLine,
                null,
                $this->formTemplateArray
            );

            return implode("\n", $contentArray);
        }
        return null;
    }

    private function templateServiceInit(EntityInfo $entityInfo): void
    {
        $this
            ->templateFileService
            ->addReplace(TemplateFileService::REPLACE_NAMESPACE, $entityInfo->getNamespace())
            // ->addReplace(TemplateFileService::REPLACE_BUNDLE_NAME, $entityInfo->getBundleName())
            ->addReplace(TemplateFileService::REPLACE_CLASS_PREFIX_NAME, $entityInfo->getName())
            ->addReplace('//', '/')
            ->addReplace('\\\\', '\\');
    }
}
