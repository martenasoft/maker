<?php

namespace MartenaSoft\Maker\Form;

use MartenaSoft\Maker\Entity\BundleElementsEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BundleElementsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('path')
            ->add('name')

            ->addEventListener(
                FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();


                if (!empty($data) && !empty($data->getExistsContent())) {
                    $form
                        ->add('content', TextareaType::class, [
                            'required' => false
                        ])
                        ->add('existsContent', TextareaType::class, [
                            'required' => false
                        ])->add('existsContentAction', ChoiceType::class, [
                            'choices' => [
                                 'Replace' => BundleElementsEntity::REPLACE_CONTENT,
                                 'Leave old' => BundleElementsEntity::LEAVE_OLD_CONTENT,
                                 'Append' => BundleElementsEntity::APPEND_CONTENT,
                            ]
                        ]);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => BundleElementsEntity::class
            ]
        );
    }
}
