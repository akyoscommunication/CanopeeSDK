<?php

namespace Akyos\CanopeeSDK\Form\Faq;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class FaqSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', SearchType::class, [
                'label' => 'Recherche',
                'required' => false,
                'attr' => [
                    'data-model' => 'search',
                    'placeholder' => "Rechercher",
                    'onkeydown' => "return (event.keyCode!=13);"
                ],
            ])
        ;
    }
}
