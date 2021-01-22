<?php

namespace MartenaSoft\Maker\Service;

use MartenaSoft\Common\Exception\CommonException;
use MartenaSoft\Maker\Entity\Bundle;
use MartenaSoft\Maker\Entity\ClassEntity;
use MartenaSoft\Maker\Entity\Controller;
use MartenaSoft\Maker\Entity\CreateBundleEntity;
use MartenaSoft\Maker\Entity\Entity;
use MartenaSoft\Maker\MartenaSoftMakerBundle;
use mysql_xdevapi\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BundleService
{
    private ParameterBagInterface $parameterBag;
    private array $config = [];
    private $mePath = '';
    public const FILE_EXISTS_ERROR_NO = 1;

    public function __construct(ParameterBagInterface $parameterBag, ?array $config = null)
    {
        $this->mePath = realpath(__DIR__ . '/../');
        $this->parameterBag = $parameterBag;
        if ($config === null) {
            $this->setConfig($parameterBag->get(MartenaSoftMakerBundle::getConfigName()));
        }

        $this->validateConfig();
    }


    public function getBundles(?string $path = null): array
    {
        $bundles = $this->getConfig();
        $result = [];

        foreach ($bundles['bundles'] as $bundle) {

            $path = $bundles['root'] . DIRECTORY_SEPARATOR . $bundle['dir'];

            foreach (new \DirectoryIterator($path) as $fileInfo) {

                $bundleInfo = $this->getBundleInfo(
                    $fileInfo->getFilename()
                );

                if ($fileInfo->isDot() || !$fileInfo->isDir() || empty($bundleInfo)) {
                    continue;
                }

                $result[$fileInfo->getFilename()] = $bundleInfo;
            }
        }
        return $result;
    }

    public function getBundle(string $name, ?string $path = null): ?Bundle
    {
        $bundlesConfig = $this->getConfig();
        $bundleInfo = $this->getBundleInfo($name, $path);

        if (!empty($bundleInfo)) {
            $entityBundle = new Bundle();
            $entityBundle
                ->setNamespace($bundleInfo['namespace'])
                ->setName($name)
                ->setRootDir($bundlesConfig['root'])
                ->setPath($bundleInfo['path']);

            foreach ($bundlesConfig['directories'] as $directoryName) {
                if (!empty($bundleInfo[$directoryName])) {
                    foreach ($bundleInfo[$directoryName] as $class) {
                        $classEntity = new ClassEntity();
                        $classEntity->setName($class['fileName']);
                        $classEntity->setContent($class['content']);
                        $entityBundle->getCollection($directoryName)->add($classEntity);
                    }
                }
            }

            return $entityBundle;
        }
        return null;
    }

    public function initContentDirectoriesAndEmptyFiles(
        CreateBundleEntity $entity,
        string $prefixName = 'Default'
    ): array {
        $return = [];
        $config = $this->getConfig();
        $templatesPath = $this->mePath . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'templates';
        $bundleRootPath = $config['root'] . DIRECTORY_SEPARATOR . $entity->getPath();

        if (!is_dir($bundleRootPath)) {
            mkdir($bundleRootPath, 0755, true);
        }

        $gitignore = file_get_contents(
            $templatesPath .
            DIRECTORY_SEPARATOR .
            'Gitignore' .
            DIRECTORY_SEPARATOR .
            'default.txt'
        );

        $return = [
            'Gitignore' => [
                'path' => $bundleRootPath ,
                'file' => '.gitignore',
                'content' => $gitignore
            ],
            'README' => [
                'path' => $bundleRootPath . DIRECTORY_SEPARATOR,
                'file' =>  'README.md',
                'content' => $entity->getDescription()
            ]
        ];


        $bundleRootPath .= DIRECTORY_SEPARATOR . 'src';

       /* if (!is_dir($bundleRootPath)) {
            mkdir($bundleRootPath, 0755, true);
        }*/

        $templateFile = $templatesPath .
            DIRECTORY_SEPARATOR .
            'Bundle' .
            DIRECTORY_SEPARATOR .
            strtolower($prefixName) . '.txt';

        if (file_exists($templateFile)) {
            $content = $this->replaceContent(file_get_contents($templateFile), $entity, $prefixName);
            $return['Bundle'] = [
                    'path' => $bundleRootPath,
                    'file' => $this->getResourceBundleName($entity) . 'Bundle.php',
                    'content' => $content
            ];
        }

        $dependencyInjectionBundleRootPath = $bundleRootPath . DIRECTORY_SEPARATOR . 'DependencyInjection';

        $templateConfigurationFile = $templatesPath .
            DIRECTORY_SEPARATOR .
            'Bundle' .
            DIRECTORY_SEPARATOR .
            'DependencyInjection' .
            DIRECTORY_SEPARATOR .
            strtolower($prefixName) . '_configuration.txt';

        if (file_exists($templateConfigurationFile)) {
            $return['DependencyInjection Configuration'] = [
                'path' => $dependencyInjectionBundleRootPath,
                'file' => 'Configuration.php',
                'content' => $this->replaceContent(file_get_contents($templateConfigurationFile), $entity, $prefixName)
            ];
        }

        $templateExtensionFile = $templatesPath .
            DIRECTORY_SEPARATOR .
            'Bundle' .
            DIRECTORY_SEPARATOR .
            'DependencyInjection' .
            DIRECTORY_SEPARATOR .
            strtolower($prefixName) . '_extension.txt';

        if (file_exists($templateExtensionFile)) {
            $return['DependencyInjection Extension'] = [
                'path' => $dependencyInjectionBundleRootPath,
                'file' => $this->getResourceBundleName($entity) . 'Extension.php',
                'content' => $this->replaceContent(file_get_contents($templateExtensionFile), $entity, $prefixName)
            ];
        }


        foreach ($entity->getModules() as $module => $index) {
            $modulePath = $bundleRootPath . DIRECTORY_SEPARATOR . $module;


            $templatePath = $templatesPath .
                DIRECTORY_SEPARATOR .
                $module .
                DIRECTORY_SEPARATOR;

            switch ($module) {
                case("Resources"):

                    $configPath = $modulePath . DIRECTORY_SEPARATOR . 'config';

                    $serviceConfigTemplate = $templatesPath .
                        DIRECTORY_SEPARATOR .
                        'Service' .
                        DIRECTORY_SEPARATOR .
                        strtolower($prefixName) . '_yaml.txt';

                    if (file_exists($serviceConfigTemplate)) {

                        $serviceFile =
                            $modulePath .
                            DIRECTORY_SEPARATOR .
                            'config' .
                            DIRECTORY_SEPARATOR .
                            'services.yaml';

                        $return['Resources Config'] = [
                            'path' => $configPath,
                            'file' => $serviceFile,
                            'content' => $this->replaceContent(
                                file_get_contents($serviceConfigTemplate), $entity, $prefixName
                            )
                        ];
                    }
                    break;
                default:
                    $return[$module] = [
                        'path' => $modulePath
                    ];
            }
        }

       return $return;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    private function saveFile(
        string $templateFile,
        string $directory,
        string $file,
        CreateBundleEntity $entity,
        string $prefixName,
        bool $isLower = false
    ): void
    {
        if (file_exists($directory . DIRECTORY_SEPARATOR . $file)) {
            throw new Exception(
                'File already exists: '. $directory . DIRECTORY_SEPARATOR . $file,
                self::FILE_EXISTS_ERROR_NO
            );
        }

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($templateFile)) {
            $content = file_get_contents($templateFile);
            $content = $this->replaceContent($content, $entity, $prefixName);
            file_put_contents($directory . DIRECTORY_SEPARATOR . $file, $content);
        }
    }

    private function replaceContent(
        string $content,
        CreateBundleEntity $entity,
        string $prefixName,
        ?array $form = null,
        ?array $to = null,
        bool $isLower = false
    ): string
    {
        $findData = [
            '__REPLACE_NAMESPACE__',
            '__REPLACE_BUNDLE_NAME__',
            '__REPLACE_PREFIX__',
            '__REPLACE_PREFIX_LC__',
            '__REPLACE_RESOURCE_BUNDLE_NAME__',
            '__REPLACE_BUNDLE_NAME_LC__',
            '__REPLACE_NAMESPACE_LC__',

        ];
        $resourceBundleName = $this->getResourceBundleName($entity);
        $replaceData = [
            $entity->getNamespace(),
            $entity->getName(),
            $prefixName,
            strtolower($prefixName),
            $resourceBundleName,
            strtolower($entity->getName()),
            strtolower(str_replace(['\\'], ['_'], $entity->getNamespace())),

        ];

        if ($form !== null) {
            $findData = array_merge($findData, $form);
        }

        if ($to !== null) {
            $replaceData = array_merge($replaceData, $to);
        }

        return str_replace($findData, $replaceData, $content);
    }

    private function getResourceBundleName(CreateBundleEntity $entity): string
    {
        return str_replace('\\', '', $entity->getNamespace());
    }

    private function getBundleInfo(string $name, ?string $basePath = null): ?array
    {
        $bundlesConfig = $this->getConfig();
        $root = $this->getRootDir();
        $pathArray = $this->getPathFormComposerJson($name);

        if (empty($pathArray)) {
            return null;
        }

        if (empty($basePath)) {
            $basePath = $root . DIRECTORY_SEPARATOR . $pathArray['path'];
        }

//dump($path); die;
        if (empty($pathArray) || empty('.') || empty('..') || empty($class = $this->findBundleClass(
                $basePath,
                $name,
                $pathArray['namespace']))) {
            return [];
        }



        $result = ['class' => $class];
        $result = array_merge($result, $pathArray);

        if (!empty($bundlesConfig['directories'])) {
            foreach ($bundlesConfig['directories'] as $directory) {
                $result[$directory] = $this->getClassesInDirectory(
                    $basePath,
                    $directory,
                    $pathArray['namespace']
                );
            }
        }

        return $result;
    }

    private function getClassesInDirectory(string $path, string $directory, string $namespace): array
    {
        $result = [];
        $rootDir = $path . DIRECTORY_SEPARATOR . $directory;
        if (!is_dir($path . DIRECTORY_SEPARATOR . $directory)) {
            return [];
        }

        foreach (new \DirectoryIterator($rootDir) as $fileInfo) {
            if ($fileInfo->getExtension() == "php") {
                $classShortName = pathinfo($fileInfo->getFilename())['filename'];
                $className = $namespace . $directory . "\\" . $classShortName;
                if (class_exists($className)) {
                    $fileRootPath = $path .
                        DIRECTORY_SEPARATOR .
                        $directory .
                        DIRECTORY_SEPARATOR .
                        $fileInfo->getFilename();

                    $result[$classShortName] = [
                        'path' => $fileRootPath,
                        'class' => $className,
                        'fileName' => $fileInfo->getFilename(),
                        'content' => file_get_contents($fileRootPath)
                    ];
                }
            }
        }
        return $result;
    }

    private function getPathFormComposerJson(string $name): ?array
    {
        if (in_array($name, ['.', '.']) || empty($name)) {
            return null;
        }

        $composerJson = file_get_contents($this->getRootDir() . DIRECTORY_SEPARATOR . 'composer.json');
        $composerJsonArray = json_decode($composerJson, true);
        foreach ($composerJsonArray['autoload']['psr-4'] as $namespace => $path) {
            list(, $name_) = explode('\\', $namespace);
            if (empty($name_)) {
                continue;
            }

            if (strtolower($name_) == strtolower($name)) {
                return [
                    'path' => preg_replace(['/\/{2,}/', '/\/{1,}$/'], ['/', ''], $path),
                    'namespace' => $namespace
                ];
            }
        }
        return null;
    }

    private function getRootDir(): string
    {
        return $this->getConfig()['root'];
    }

    private function findBundleClass(string $path, string $name, string $namespace): ?string
    {

        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if (strrpos($fileInfo->getFilename(), "Bundle.php") !== false) {
                $className = $namespace . pathinfo($fileInfo->getFilename())['filename'];
                if (class_exists($className)) {
                    return $className;
                }
            }
        }
        return null;
    }

    private function validateConfig(): void
    {
        if (empty($this->config)) {
            throw new CommonException("Config can not be empty empty");
        }

        if (empty($this->config['bundles'])) {
            throw new CommonException("Config [bundles] parameter can not be empty empty");
        }

        if (empty($this->config['root'])) {
            throw new CommonException("Config [root] parameter can not be empty empty");
        }

        if (!is_dir($this->config['root'])) {
            throw new CommonException("directory: {$this->config['root']} not found");
        }
    }
}