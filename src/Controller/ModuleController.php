<?php

namespace MartenaSoft\Maker\Controller;

use MartenaSoft\Common\Controller\AbstractAdminBaseController;
use MartenaSoft\Maker\Service\BundleService;
use Symfony\Component\HttpFoundation\Response;

class ModuleController extends AbstractAdminBaseController
{
    public function index(BundleService $bundleService): Response
    {
        $bundles = $this->bundleService->getBundles();

        return $this->render('@MartenaSoftMaker/module/index.html.twig', [
            'bundles' => $bundles
        ]);
    }
}
