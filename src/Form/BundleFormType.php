<?php

namespace MartenaSoft\Maker\Form;

use MartenaSoft\Maker\Entity\Bundle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BundleFormType extends AbstractType
{
    private FormFactoryInterface $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'sysAction',
                HiddenType::class,
                [
                    'required' => false,

                ]
            )
            ->add('name')
            ->add('rootDir')
            ->add('path')
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $data = $event->getData();
                    $form = $event->getForm();

                    foreach ($data->getArrayCollections() as $classDir => $arrayCollection) {
                        $form->add(
                            $classDir,
                            CollectionType::class,
                            [
                                'entry_type' => EntityFormType::class,
                                'entry_options' => ['label' => false],
                                'allow_add' => true
                            ]
                        );
                    }
                }
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Bundle::class
            ]
        );
    }
}
