<?php

namespace MartenaSoft\Maker\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MartenaSoft\Common\Controller\AbstractAdminBaseController;
use MartenaSoft\Maker\Entity\EntityField;
use MartenaSoft\Maker\Entity\EntityInfo;
use MartenaSoft\Maker\Form\EntityInfoFormType;
use MartenaSoft\Maker\Service\BundleService;
use MartenaSoft\Maker\Service\EntityService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityController extends AbstractAdminBaseController
{

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        BundleService $bundleService
    ) {
        parent::__construct($entityManager, $logger, $eventDispatcher);
        $this->bundleService = $bundleService;
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

    public function create(Request $request, EntityService $entityService, ?string $bundleName = null): Response
    {
        $entityInfo = new EntityInfo();

        if (!empty($bundleName) && !empty($bundleInfo = $this->bundleService->getBundle($bundleName))) {
            $entityInfo->setNamespace($bundleInfo->getNamespace());
            $entityInfo->setBundleName($bundleInfo->getName());
        }

        if (!empty($formData = $request->request->get('entity_info_form'))) {
            if (!empty($formData['entityField'])) {
                foreach ($formData['entityField'] as $field) {
                    $entityInfo->getEntityField()->add($this->getEntityFieldNewType());
                }
            }
        }

        $entityInfo->getEntityField()->add($this->getEntityFieldNewType());
        $form = $this->createForm(EntityInfoFormType::class, $entityInfo);

        $form->handleRequest($request);
        $content = '';
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            if ($formData->getSysAction() == 'add-type') {
                $form = $this->createForm(EntityInfoFormType::class, $entityInfo);
            } else {
                $content = $entityService->collectData($formData);
            }
        }
        dump($content);
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
