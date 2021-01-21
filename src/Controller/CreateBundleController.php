<?php

namespace MartenaSoft\Maker\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MartenaSoft\Common\Controller\AbstractAdminBaseController;
use MartenaSoft\Maker\DependencyInjection\Configuration;
use MartenaSoft\Maker\Entity\Bundle;
use MartenaSoft\Maker\Entity\ClassEntity;
use MartenaSoft\Maker\Entity\Controller;
use MartenaSoft\Maker\Entity\CreateBundleEntity;
use MartenaSoft\Maker\Form\BundleFormType;
use MartenaSoft\Maker\Form\CreateBundleFormType;
use MartenaSoft\Maker\Service\BundleService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use function Symfony\Component\String\u;

class CreateBundleController extends AbstractAdminBaseController
{
    private BundleService $bundleService;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        BundleService $bundleService
    )
    {
        parent::__construct($entityManager, $logger, $eventDispatcher);
        $this->bundleService = $bundleService;
    }

    public function index(Request $request): Response
    {
        $bundles = $this->bundleService->getBundles();
        return $this->render('@MartenaSoftMaker/bundle/index.html.twig', [
            'bundles' => $bundles
        ]);
    }

    public function create(Request $request): Response
    {
        $entityBundle = new CreateBundleEntity();

        $entityBundle->setPath($this->bundleService->getConfig()['root']);

        $form = $this->createForm(CreateBundleFormType::class, $entityBundle);
        $bundle = null;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bundleService->createDirectoriesAndEmptyFiles($form->getData());

            } catch (\Throwable $exception) {
                throw $exception;
            }
        }


        return $this->render('@MartenaSoftMaker/bundle/create.html.twig', [
            'form' => $form->createView(),
            'directories' => Configuration::getDirectories()
        ]);
    }

    public function save(Request $request, ?string $slug = null): Response
    {
        if (!empty($slug)) {
            $entityBundle = $this->bundleService->getBundle($slug);
        } else {
            $entityBundle = new Bundle();
        }

        $form = $this->createForm(BundleFormType::class, $entityBundle);
        $bundle = null;

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->errorMessage("Error save");
            } else {

                $sysCommand = $form->getData()->getSysAction();
                $command = '';
                $controller = '';

                if (strpos($sysCommand, '-') !== false) {
                    list($command, $controller) = explode('-', $sysCommand);
                }

                switch ($command) {
                    case 'add' :
                        $entityBundle->getCollection($controller)->add(new ClassEntity());
                        $form = $this->createForm(BundleFormType::class, $entityBundle);
                        break;
                }

                $this->successMessage("Data Saved");
            }
        }

        return $this->render('@MartenaSoftMaker/bundle/save.html.twig', [
            'form' => $form->createView(),
            'directories' => Configuration::getDirectories()
        ]);
    }

    public function add(RouterInterface $router, string $name, string $bundlename): Response
    {
        switch ($name) {
            case "Entity":
                return $this->redirectToRoute('admin_maker_entity_create', [
                    'bundleName' => $bundlename
                ]);
            default:
                $name = 'admin_maker_'.u($name)->snake().'_create';
        }

        if ($router->getRouteCollection()->get($name)) {
            return $this->redirectToRoute($name);
        }
        return $this->render('@MartenaSoftMaker/bundle/add.html.twig');
    }

    public function changeElement(RouterInterface $router, string $name, string $bundlename): Response
    {
        switch ($name) {
            case "Entity":
                return $this->redirectToRoute('admin_maker_entity_edit', [
                    'bundleName' => $bundlename,
                    'name' => $name
                ]);
            default:
                $name = 'admin_maker_'.u($name)->snake().'_edit';
        }

        if ($router->getRouteCollection()->get($name)) {
            return $this->redirectToRoute($name);
        }
        return $this->render('@MartenaSoftMaker/bundle/add.html.twig');
    }
}
