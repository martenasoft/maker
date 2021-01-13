<?php

namespace MartenaSoft\Maker\Service;

use MartenaSoft\Common\Exception\CommonException;
use MartenaSoft\Maker\Entity\Bundle;
use MartenaSoft\Maker\Entity\ClassEntity;
use MartenaSoft\Maker\Entity\Controller;
use MartenaSoft\Maker\Entity\CreateBundleEntity;
use MartenaSoft\Maker\Entity\Entity;
use MartenaSoft\Maker\MartenaSoftMakerBundle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BundleService
{
    private ParameterBagInterface $parameterBag;
    private array $config = [];
    private $mePath = '';

    public function __construct(ParameterBagInterface $parameterBag, ?array $config = null)
    {
        $this->mePath = realpath(__DIR__ . '/../');
        $this->parameterBag = $parameterBag;
        if ($config === null) {
            $this->setConfig($parameterBag->get(MartenaSoftMakerBundle::getConfigName()));
        }

        $this->validateConfig();
    }


    public function getBundles(): array
    {
        $bundles = $this->getConfig();
        $result = [];

        foreach ($bundles['bundles'] as $bundle) {
            $path = $bundles['root'] . DIRECTORY_SEPARATOR . $bundle['dir'];
            foreach (new \DirectoryIterator($path) as $fileInfo) {
                if ($fileInfo->isDot() ||
                    !$fileInfo->isDir() ||
                    empty(
                    ($bundleInfo = $this->getBundleInfo(
                        $path,
                        $fileInfo->getFilename()
                    ))
                    )) {
                    continue;
                }

                $result[$fileInfo->getFilename()] = $bundleInfo;
            }
        }
        return $result;
    }

    public function getBundle(string $name): ?Bundle
    {
        $bundlesConfig = $this->getConfig();
        $path = $bundlesConfig['root'] . DIRECTORY_SEPARATOR . $name;
        $bundleInfo = $this->getBundleInfo($path, $name);

        if (!empty($bundleInfo)) {
            $entityBundle = new Bundle();
            $entityBundle
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

    public function createDirectoriesAndEmptyFiles(CreateBundleEntity $entity, string $prefixName = 'Default'): void
    {
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

        file_put_contents($bundleRootPath . DIRECTORY_SEPARATOR . '.gitignore', $gitignore);
        file_put_contents($bundleRootPath . DIRECTORY_SEPARATOR . 'README.md', $entity->getDescription());

        $bundleRootPath .= DIRECTORY_SEPARATOR . 'src';

        if (!is_dir($bundleRootPath)) {
            mkdir($bundleRootPath, 0755, true);
        }

        $templateFile = $templatesPath .
            DIRECTORY_SEPARATOR .
            'Bundle' .
            DIRECTORY_SEPARATOR .
            strtolower($prefixName) . '.txt';


        $this->saveFile(
            $templateFile,
            $bundleRootPath,
            $this->getResourceBundleName($entity) . 'Bundle.php',
            $entity,
            $prefixName
        );

        $dependencyInjectionBundleRootPath = $bundleRootPath . DIRECTORY_SEPARATOR . 'DependencyInjection';

        if (!is_dir($dependencyInjectionBundleRootPath)) {
            mkdir($dependencyInjectionBundleRootPath, 0755, true);
        }

        $templateConfigurationFile = $templatesPath .
            DIRECTORY_SEPARATOR .
            'Bundle' .
            DIRECTORY_SEPARATOR .
            'DependencyInjection' .
            DIRECTORY_SEPARATOR .
            strtolower($prefixName) . '_configuration.txt';


        $this->saveFile(
            $templateConfigurationFile,
            $dependencyInjectionBundleRootPath,
            'Configuration.php',
            $entity,
            $prefixName
        );

        $templateExtensionFile = $templatesPath .
            DIRECTORY_SEPARATOR .
            'Bundle' .
            DIRECTORY_SEPARATOR .
            'DependencyInjection' .
            DIRECTORY_SEPARATOR .
            strtolower($prefixName) . '_extension.txt';


        $this->saveFile(
            $templateExtensionFile,
            $dependencyInjectionBundleRootPath,
            $this->getResourceBundleName($entity) . 'Extension.php',
            $entity,
            $prefixName
        );


        foreach ($entity->getModules() as $module => $index) {
            $modulePath = $bundleRootPath . DIRECTORY_SEPARATOR . $module;

            if (!is_dir($modulePath)) {
                mkdir($modulePath, 0755, true);
            }

            file_put_contents($modulePath . DIRECTORY_SEPARATOR . '.gitignore', $gitignore);

            $templatePath = $templatesPath .
                DIRECTORY_SEPARATOR .
                $module .
                DIRECTORY_SEPARATOR;

            switch ($module) {
                case("Resource"):

                    $configPath = $modulePath . DIRECTORY_SEPARATOR . 'config';
                    $viewPath = $modulePath . DIRECTORY_SEPARATOR . 'views';

                    if (!is_dir($configPath)) {
                        mkdir($configPath, 0755, true);
                    }

                    $serviceConfigTemplate = $templatesPath .
                        DIRECTORY_SEPARATOR .
                        'Service' .
                        DIRECTORY_SEPARATOR .
                        strtolower($prefixName) . '_yaml.txt';

                    if (file_exists($serviceConfigTemplate)) {
                        $serviceContent = file_get_contents($serviceConfigTemplate);
                        $serviceFile =
                            $modulePath .
                            DIRECTORY_SEPARATOR .
                            'config' .
                            DIRECTORY_SEPARATOR .
                            'services.yaml';

                        $serviceContent = $content = $this->replaceContent($serviceContent, $entity, $prefixName);
                        file_put_contents($serviceFile, $serviceContent);
                    }

                    /*  if (in_array('Controller', $entity->getModules())) {
                          $configRoutePath = $configPath . DIRECTORY_SEPARATOR . 'routes';

                          $templateFile = $templatesPath .
                              DIRECTORY_SEPARATOR .
                              'Route' .
                              DIRECTORY_SEPARATOR .
                              strtolower($prefixName) . '.txt';


                          $this->saveFile(
                              $templateFile,
                              $configRoutePath,
                              'all.yaml',
                              $entity,
                              $prefixName,
                              true
                          );

                          $resourceViewTemplateFile = $templatesPath .
                              DIRECTORY_SEPARATOR .
                              'View' .
                              DIRECTORY_SEPARATOR .
                              strtolower($prefixName) . '.txt';

                          $viewIndexPath = $viewPath . DIRECTORY_SEPARATOR . strtolower($prefixName);

                          $this->saveFile(
                              $resourceViewTemplateFile,
                              $viewIndexPath,
                              'index.html.twig',
                              $entity,
                              $prefixName
                          );

                      }*/

                    break;

                default:
                    /* $templatePath .= strtolower($prefixName) . '.txt';
                     $fileName = $modulePath . DIRECTORY_SEPARATOR . $prefixName . $module . '.php';

                     if (file_exists($templatePath)) {
                         $content = file_get_contents($templatePath);
                         $content = $this->replaceContent($content, $entity, $prefixName);
                         file_put_contents($fileName, $content);
                     }*/
            }
        }

        dump($config, $entity);
        //  mkdir();
        die;
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

    private function getBundleInfo(string $path, string $name): ?array
    {
        $bundlesConfig = $this->getConfig();
        $root = $this->getRootDir();

        if (!empty($path = $this->getPathFormComposerJson($name)) &&
            !empty(
            $class = $this->findBundleClass(
                $root . DIRECTORY_SEPARATOR . $path['path'],
                $path['namespace']
            )
            )) {
            $basePath = $root . DIRECTORY_SEPARATOR . $path['path'];
            $result = ['class' => $class];
            $result = array_merge($result, $path);

            if (!empty($bundlesConfig['directories'])) {
                foreach ($bundlesConfig['directories'] as $directory) {
                    $result[$directory] = $this->getClassesInDirectory(
                        $basePath,
                        $directory,
                        $path['namespace']
                    );
                }
            }

            return $result;
        }

        return [];
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
        $composerJson = file_get_contents($this->getRootDir() . DIRECTORY_SEPARATOR . 'composer.json');
        $composerJsonArray = json_decode($composerJson, true);
        foreach ($composerJsonArray['autoload']['psr-4'] as $namespace => $path) {
            list(, $name_) = explode('\\', $namespace);
            if ($name_ == ucfirst($name)) {
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

    private function findBundleClass(string $path, string $namespace): ?string
    {
        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if (strrpos($fileInfo->getFilename(), "Bundle.php") !== false
            ) {
                $className = $namespace . pathinfo($fileInfo->getFilename())['filename'];
                if (class_exists($className)) {
                    return $className;
                }
            }
        }
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