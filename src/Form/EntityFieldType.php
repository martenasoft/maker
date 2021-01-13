<?php

namespace MartenaSoft\Maker\Form;

use MartenaSoft\Maker\Entity\EntityField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityField = new EntityField();
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => $entityField->getTypes(),
                'required' => false,
                'label' => false
            ])
            ->add('templateName', ChoiceType::class, [
                'choices' => $entityField->getNameTemplates(),
                'required' => false,
                'label' => false
            ])
            ->add('isForm', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => EntityField::class,

            ]
        );
    }
}

