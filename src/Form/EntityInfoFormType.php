<?php

namespace MartenaSoft\Maker\Form;

use MartenaSoft\Maker\Entity\EntityInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityInfo = new EntityInfo();

        $builder
            ->add('namespace')
            ->add('bundleName')
            ->add('name')
            ->add('isDatabase', CheckboxType::class, [
                'required' => false
            ])
            ->add('entityField', CollectionType::class, [
                'entry_type' => EntityFieldType::class
            ])

            ->add('sysAction', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => EntityInfo::class
            ]
        );
    }
}

