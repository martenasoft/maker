<?php

namespace MartenaSoft\Maker\Form;

use MartenaSoft\Maker\DependencyInjection\Configuration;
use MartenaSoft\Maker\Entity\CreateBundleEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateBundleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = new CreateBundleEntity();
        $builder
            ->add('path')
            ->add('namespace')
            ->add('name')
            ->add('description', TextareaType::class)
            ->add('isInitGitRepository', CheckboxType::class)
            ->add('gitUrl')
            ->add('isInitComposerJson', CheckboxType::class, [
               // 'data' => true
            ])
            ->add('modules', ChoiceType::class, [
                'choices' => [array_flip($entity->getModules())],
                'expanded' => true,
                'multiple' => true,
                'data' =>  array_flip($entity->getModules()),
            ])  ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $data = $event->getData();
                    $form = $event->getForm();

                    $form->add(
                        'data',
                        CollectionType::class,
                        [
                            'entry_type' => BundleElementsFormType::class,
                            'entry_options' => ['label' => false],
                            'allow_add' => true
                        ]
                    );

                }
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => CreateBundleEntity::class
            ]
        );
    }
}
