<?php

namespace MartenaSoft\Maker\Service;

use MartenaSoft\Maker\Entity\Bundle;
use MartenaSoft\Maker\Entity\EntityInfo;
use function Symfony\Component\String\u;

class EntityService
{
    private TemplateFileService $templateFileService;
    private BundleService $bundleService;
    private EmbedCodeService $codeService;

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

    public function getDataFormFile(EntityInfo $entityInfo, Bundle $bundleInfo, string $name): string
    {
        $arrayCollection = $bundleInfo->getArrayCollections();
        if (!empty($entityData = $arrayCollection['Entity'])) {
            foreach ($entityData as $entityItem) {
                if ($entityItem->getName() == $name.'.php') {
                    $this->codeService->setContent($entityItem->getContent());
                    $data = $this->codeService->findMethod('[a-zA-Z0-9_]+');
                    dump($data); die;
                    break;
                }
            }
        }
        return '';
    }

    public function collectData(EntityInfo $entityInfo, ?string $content = null): string
    {
        $this->templateServiceInit($entityInfo);

        $bundleData = $this->bundleService->getBundle($entityInfo->getBundleName());

        if (empty($content)) {
            $content = $this->templateFileService->getTemplateContent('Entity');
        }

        $typesFile = $this->templateFileService->getTemplatePath().'/Entity/types.php';

        $types = require $typesFile;

        $content_ = $this->templateFileService->getPlacesToInsertVariablesAndFunctions($content);

        $phpVarResult = '';
        $getter = '';
        $setter = '';

        if (!empty($fields = $entityInfo->getEntityField())) {
            foreach ($fields as $field) {
                if (is_numeric($field->getType()) && !empty($types[$field->getType()])) {

                    $typeName = $types[$field->getType()]['type'];

                    $phpVar = !empty($types[$field->getType()]['php_var'])
                            ? $types[$field->getType()]['php_var'] : $types['php_var'];

                    $phpGetter = !empty($types[$field->getType()]['getter'])
                        ? $types[$field->getType()]['getter'] : $types['getter'];

                    $phpSetter = !empty($types[$field->getType()]['setter'])
                        ? $types[$field->getType()]['setter'] : $types['setter'];

                    $nameCamel = u($field->getName())->camel();

                    $this
                        ->templateFileService
                        ->addReplace(TemplateFileService::REPLACE_VAR_TYPE, $typeName)
                        ->addReplace(TemplateFileService::REPLACE_FUNCTION_NAME, ucfirst($nameCamel))
                        ->addReplace(TemplateFileService::REPLACE_VAR_NAME, $field->getName())
                    ;

                    $phpVarResult .= $this->templateFileService->replace($phpVar)."\n";
                    $getter .= $this->templateFileService->replace($phpGetter)."\n";
                    $setter .= $this->templateFileService->replace($phpSetter)."\n";

                }
            }
        }

        return $this
            ->templateFileService
            ->addReplace(TemplateFileService::REPLACE_INSERT_NEW_VARS, "\n\t". $phpVarResult)
            ->addReplace(TemplateFileService::REPLACE_INSERT_NEW_FUNC, "\n\t".$getter . "\n\t".$setter )
            ->replace($content_);
    }



    private function templateServiceInit(EntityInfo $entityInfo): void
    {
        $this
            ->templateFileService
            ->addReplace(
                TemplateFileService::REPLACE_NAMESPACE,
                preg_replace('/\\\+$/', '', $entityInfo->getNamespace()))
            ->addReplace(TemplateFileService::REPLACE_BUNDLE_NAME, $entityInfo->getBundleName())
            ->addReplace(TemplateFileService::REPLACE_CLASS_PREFIX_NAME, $entityInfo->getName())
        ;
    }
}
