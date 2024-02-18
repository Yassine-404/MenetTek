<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Projectweb;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('NomP')
            ->add('PrixP')
            ->add('DescP')
            ->add('StockP')
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'type'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projectweb::class,
        ]);
    }
}
