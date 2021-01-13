<?php

namespace MartenaSoft\Maker\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use function PHPUnit\Framework\returnArgument;

class TemplateFileService
{
    public const REPLACE_NAMESPACE = '__REPLACE_NAMESPACE__';
    public const REPLACE_BUNDLE_NAME = '__REPLACE_BUNDLE_NAME__';
    public const REPLACE_CLASS_PREFIX_NAME = '__REPLACE_PREFIX__';
    public const REPLACE_RESOURCE_BUNDLE_NAME = '__REPLACE_RESOURCE_BUNDLE_NAME__';
    public const REPLACE_PREFIX_LC = '__REPLACE_PREFIX_LC__';
    public const REPLACE_VAR_TYPE = '__REPLACE_VAR_TYPE__';
    public const REPLACE_VAR_NAME = '__REPLACE_VAR_NAME__';
    public const REPLACE_FUNCTION_NAME = '__REPLACE_FUNCTION_NAME__';
    public const REPLACE_INSERT_NEW_VARS = '/* new_vars */';
    public const REPLACE_INSERT_NEW_FUNC = '/* new_func */';

    private string $templatesPath;
    private array $replace = [];

    public function __construct()
    {
        $this->setTemplatesPath(realpath(__DIR__ .
                DIRECTORY_SEPARATOR .
                '..' .
                DIRECTORY_SEPARATOR
            ) .
            DIRECTORY_SEPARATOR .
            'Resources' .
            DIRECTORY_SEPARATOR .
            'templates');
    }

    public function setTemplatesPath(string $templatesPath): self
    {
        $this->templatesPath = $templatesPath;
        return $this;
    }

    public function getTemplatePath(): string
    {
        return $this->templatesPath;
    }

    public function setReplace(array $replace, bool $isMarge = false): self
    {
        if ($isMarge) {
            $this->replace = array_merge($this->replace, $replace);
        } else {
            $this->replace = $replace;
        }
        return $this;
    }

    public function addReplace(string $find, string $replace): self
    {
        $this->replace[$find] = $replace;
        return $this;
    }

    public function replace(string $content): string
    {
        return str_replace(array_keys($this->replace), array_values($this->replace), $content);
    }

    public function getTemplateContent(string $dir, string $fileName = 'default.txt'): ?string
    {
        $dir_ = $this->getTemplatePath() . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($dir_) && !empty($content = file_get_contents($dir_))) {
            return $this->replace($content);
        }

        return null;
    }

    public function getPlacesToInsertVariablesAndFunctions(string $content): string
    {
        $result = '';
        if (!empty($content)) {
            $contentArray = explode("\n", $content);

            $firstFunctionLine = 0;
            $openClassLineNun = 0;
            $endLine = 0;

            for($i = 0, $f = $size = count($contentArray); $i < $size; $i++, $f--) {
                $line = $contentArray[$i];

                if ($openClassLineNun == 0 && strpos($line, "{") !== false) {
                    $openClassLineNun = $i;
                }

                if (strpos($line, 'function') !== false) {
                    if ($firstFunctionLine == 0) {
                        $firstFunctionLine = $i + 1;
                        $contentArray[$i - 1] .= "\n ".self::REPLACE_INSERT_NEW_VARS." \n";
                    }
                }

                if ($endLine == 0 && isset($contentArray[$f]) && strpos($contentArray[$f], '}') !== false) {
                    $endLine = $f;
                    $contentArray[$f - 1] .= self::REPLACE_INSERT_NEW_FUNC;
                }
            }
        }

        if ($firstFunctionLine == 0 && $openClassLineNun > 0) {
            $contentArray[$openClassLineNun] .= self::REPLACE_INSERT_NEW_VARS;
        }

        $result = implode("\n", $contentArray);
        return $result;
    }
}
