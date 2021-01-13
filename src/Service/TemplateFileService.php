<?php

namespace MartenaSoft\Maker\Service;

use Symfony\Component\HttpKernel\KernelInterface;

class TemplateFileService
{
    private string $templatesPath;
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->templatesPath = realpath(__DIR__.'/../../');
    }


}