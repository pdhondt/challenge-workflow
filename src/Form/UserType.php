<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class)
            ->add('firstname')
            ->add('lastName')
            ->add('roles', ChoiceType::class, array(
                'label' => 'agent role',
                'choices'=>array('first line agent' => 'ROLE_AGENT_1', 'second line agent' =>'ROLE_AGENT_2'),
//                'choice_label' => 'role',
                'expanded' => false,
                'multiple' => true,
            ))
            ->add('password')
            ->add('email')

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
