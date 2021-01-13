<?php

namespace MartenaSoft\Maker\Controller;

use MartenaSoft\Common\Controller\AbstractAdminBaseController;
use MartenaSoft\Maker\Entity\EntityField;
use MartenaSoft\Maker\Entity\EntityInfo;
use MartenaSoft\Maker\Form\EntityInfoFormType;
use MartenaSoft\Maker\Service\BundleService;
use MartenaSoft\Maker\Service\EntityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityController extends AbstractAdminBaseController
{
    public function index(BundleService $bundleService): Response
    {
        $bundles = $bundleService->getBundles();

        return $this->render('@MartenaSoftMaker/entity/index.html.twig', [
            'bundles' => $bundles
        ]);
    }

    public function create(Request $request, EntityService $entityService): Response
    {
        $entityInfo = new EntityInfo();

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

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            if ($formData->getSysAction() == 'add-type') {
                $form = $this->createForm(EntityInfoFormType::class, $entityInfo);
            } else {
                $content = $entityService->collectData($formData);
            }

        }

        return $this->render('@MartenaSoftMaker/entity/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function getEntityFieldNewType(): EntityField
    {
        $entityField = new EntityField();
        return $entityField;
    }
}
