<?php

namespace MartenaSoft\Maker\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MartenaSoft\Common\Controller\AbstractAdminBaseController;
use MartenaSoft\Maker\Entity\EntityField;
use MartenaSoft\Maker\Entity\EntityInfo;
use MartenaSoft\Maker\Form\EntityInfoFormType;
use MartenaSoft\Maker\Service\BundleService;
use MartenaSoft\Maker\Service\EntityService;
use MartenaSoft\Maker\Service\FormService;
use MartenaSoft\Maker\Service\SaverService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityController extends AbstractAdminBaseController
{

    private SaverService $saverService;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        BundleService $bundleService,
        SaverService $saverService
    )
    {
        parent::__construct($entityManager, $logger, $eventDispatcher);
        $this->bundleService = $bundleService;
        $this->saverService = $saverService;
    }

    public function index(): Response
    {
        $bundles = $this->bundleService->getBundles();

        return $this->render(
            '@MartenaSoftMaker/entity/index.html.twig',
            [
                'bundles' => $bundles
            ]
        );
    }


    public function edit(Request $request,
                         EntityService $entityService,
                         FormService $formService,
                         string $name,
                         string $bundleName

    ): Response
    {
        $entityInfo = new EntityInfo();

        if (!empty($bundleInfo = $this->bundleService->getBundle($bundleName))) {

            $entityInfo->setNamespace($bundleInfo->getNamespace());
            $entityInfo->setBundleName($bundleInfo->getName());
            $content = $entityService->getDataFormFile($entityInfo, $bundleInfo, $name);
        }

        $formData = $request->request->get('entity_info_form');

        if (!empty($formData) && !empty($formData['entityField'])) {
            foreach ($formData['entityField'] as $field) {
                $entityInfo->getEntityField()->add($this->getEntityFieldNewType());
            }
        }

        $isAddElement = !empty($formData['sysAction']) && $formData['sysAction'] == 'add-type';

        if ($isAddElement) {
            $entityInfo->getEntityField()->add($this->getEntityFieldNewType());
        }

        $path = $this->saverService->getPathByNamespace($entityInfo->getNamespace(), true);
        $entityInfo->setBundlePath($path);

        $form = $this->createForm(EntityInfoFormType::class, $entityInfo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            if ($formData->getSysAction() == 'add-type') {
                // $form = $this->createForm(EntityInfoFormType::class, $entityInfo);
            } else {

                $contentEntity = $entityService->collectData($formData);
                $contentForm = $formService->collectData($formData);
                $templateContent = $formService->getTemplate();

                $this->saverService->saveEntity(
                    $formData,
                    $contentEntity
                )->saveForm(
                    $formData,
                    $contentForm
                )->saveFormTemplate(
                    $formData,
                    $contentEntity
                );


                dump($templateContent, $contentEntity, $contentForm);

                //$this->saverService
                die;
            }
        }

        return $this->render(
            '@MartenaSoftMaker/entity/create.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    public function create(
        Request $request,
        EntityService $entityService,
        FormService $formService,
        ?string $bundleName = null
    ): Response
    {
        $entityInfo = new EntityInfo();

        if (!empty($bundleName) && !empty($bundleInfo = $this->bundleService->getBundle($bundleName))) {
            $entityInfo->setNamespace($bundleInfo->getNamespace());
            $entityInfo->setBundleName($bundleInfo->getName());
        }

        $formData = $request->request->get('entity_info_form');

        if (!empty($formData) && !empty($formData['entityField'])) {
            foreach ($formData['entityField'] as $field) {
                $entityInfo->getEntityField()->add($this->getEntityFieldNewType());
            }
        }

        $isAddElement = !empty($formData['sysAction']) && $formData['sysAction'] == 'add-type';

        if ($isAddElement) {
            $entityInfo->getEntityField()->add($this->getEntityFieldNewType());
        }

        $path = $this->saverService->getPathByNamespace($entityInfo->getNamespace(), true);
        $entityInfo->setBundlePath($path);

        $form = $this->createForm(EntityInfoFormType::class, $entityInfo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            if ($formData->getSysAction() == 'add-type') {
                // $form = $this->createForm(EntityInfoFormType::class, $entityInfo);
            } else {

                $contentEntity = $entityService->collectData($formData);
                $contentForm = $formService->collectData($formData);

                $this->saverService->saveEntity(
                    $formData,
                    $contentEntity
                )->saveForm(
                    $formData,
                    $contentForm
                )->saveFormTemplate(
                    $formData,
                    $contentEntity
                );

                return $this->redirectToRoute('admin_maker_entity_index');

            }
        }

        return $this->render(
            '@MartenaSoftMaker/entity/create.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    private function getEntityFieldNewType(): EntityField
    {
        $entityField = new EntityField();
        return $entityField;
    }
}
