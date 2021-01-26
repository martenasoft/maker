<?php

namespace MartenaSoft\Maker\Service;

use Doctrine\Common\Collections\ArrayCollection;
use MartenaSoft\Maker\Entity\BundleElementsEntity;
use MartenaSoft\Maker\Entity\EntityInfo;
use MartenaSoft\Maker\MartenaSoftMakerBundle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function Symfony\Component\String\u;

class SaverService
{
    private ParameterBagInterface $parameterBag;
    private array $config = [];
    private $mePath = '';
    private int $directoryMode = 0755;
    private int $fileMode = 0644;

    public function __construct(ParameterBagInterface $parameterBag, ?array $config = null)
    {
        $this->mePath = realpath(__DIR__ . '/../');
        $this->parameterBag = $parameterBag;
        if ($config === null) {
            $this->setConfig($parameterBag->get(MartenaSoftMakerBundle::getConfigName()));
        }
    }

    public function getPathByNamespace(string $namespace, bool $isRootPath = true): ?string
    {
        $return = '';
        if (!empty($namespace) && file_exists($this->config['root'].DIRECTORY_SEPARATOR.'composer.json')) {
            $composerJson = file_get_contents($this->config['root'].DIRECTORY_SEPARATOR.'composer.json');
            $composerJsonArray = json_decode($composerJson, true);
            if (isset($composerJsonArray['autoload']['psr-4'][$namespace])) {
                $composerJsonArray['autoload']['psr-4'][$namespace] =
                    preg_replace('/\/$/', '', $composerJsonArray['autoload']['psr-4'][$namespace]);

                if ($isRootPath) {
                    $composerJsonArray['autoload']['psr-4'][$namespace] =
                        $this->config['root'].DIRECTORY_SEPARATOR.$composerJsonArray['autoload']['psr-4'][$namespace];
                }

                return preg_replace(
                    '/\\'.DIRECTORY_SEPARATOR.'{2,}/',
                    DIRECTORY_SEPARATOR,
                    $composerJsonArray['autoload']['psr-4'][$namespace]
                );

            }
        }
        return null;
    }

    public function saveEntity(EntityInfo $entityInfo, string $content): self
    {
        if (!empty($this->config['directories']) && in_array('Entity', $this->config['directories'])) {
            $entityPath = $entityInfo->getBundlePath() . DIRECTORY_SEPARATOR . 'Entity';
            $name = ucfirst($entityInfo->getName()) .'.php';
            $this->save($name, $entityPath, $content);
        }
        return $this;
    }

    public function saveForm(EntityInfo $entityInfo, string $content): self
    {
        if (!empty($this->config['directories']) && in_array('Form', $this->config['directories'])) {
            $entityPath = $entityInfo->getBundlePath() . DIRECTORY_SEPARATOR . 'Form';
            $name = ucfirst($entityInfo->getName()) .'FormType.php';
            $this->save($name, $entityPath, $content);
        }
        return $this;
    }

    public function saveFormTemplate(EntityInfo $entityInfo, string $content): self
    {
        if (!empty($this->config['directories']) && in_array('Form', $this->config['directories'])) {
            $entityPath = $entityInfo->getBundlePath() . DIRECTORY_SEPARATOR . 'Resources/views/'.
                u($entityInfo->getName())->snake();
            $name = 'form.html.twig';
            $this->save($name, $entityPath, $content);
        }
        return $this;
    }

    public function saveCreateBundle(ArrayCollection $data): void
    {
        foreach ($data as $item) {
            if ($item instanceof BundleElementsEntity) {
                if (!empty($item->getExistsContent())) {
                    switch ($item->getExistsContentAction()) {
                        case BundleElementsEntity::LEAVE_OLD_CONTENT:
                            $item->setContent($item->getExistsContent());
                            break;

                        case BundleElementsEntity::APPEND_CONTENT:
                            $item->setContent($item->getExistsContent() . $item->getContent());
                            break;
                    }
                }

                $this->save($item->getName(), $item->getPath(), $item->getContent());
            }
        }
    }


    public function save(string $fileName,  string $dir, string $content = ''): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, $this->getDirectoryMode(), true);
        }

        if (empty($content) || empty($fileName)) {
            return;
        }

        $file = preg_replace(
            '/\\'.DIRECTORY_SEPARATOR.'{2,}/',
            DIRECTORY_SEPARATOR,
            $dir . DIRECTORY_SEPARATOR .$fileName
        );

        file_put_contents($file, $content);
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getDirectoryMode(): int
    {
        return $this->directoryMode;
    }

    public function setDirectoryMode(int $directoryMode): self
    {
        $this->directoryMode = $directoryMode;
        return $this;
    }

    public function getFileMode(): int
    {
        return $this->fileMode;
    }

    public function setFileMode(int $fileMode): self
    {
        $this->fileMode = $fileMode;
        return $this;
    }

}
