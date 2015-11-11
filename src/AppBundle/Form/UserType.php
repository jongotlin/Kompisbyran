<?php

namespace AppBundle\Form;

use AppBundle\Enum\Languages;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Enum\Countries;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', 'text', ['label' => 'Förnamn'])
            ->add('lastName', 'text', ['label' => 'Efternamn'])
            ->add('wantToLearn', 'choice', [
                'expanded' => true,
                'label' => 'Vill du',
                'choices' => [
                    true => 'Förbättra din svenska',
                    false => 'Hjälpa någon att förbättra sin svenska',
                ],
                'choice_value' => function ($currentChoiceKey) {
                    return $currentChoiceKey ? 'true' : 'false';
                }
            ])
            ->add('categories', 'entity', [
                    'class' => 'AppBundle:Category',
                    'multiple' => true,
                    'expanded' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                    },
                    'property' => 'name',
                    'label' => 'Vilka är dina intressen?',
                ]
            )
            ->add('age', 'choice', [
                'label' => 'Ålder',
                'choices' => array_combine(range(18, 100), range(18, 100)),
            ])
            ->add('gender', 'choice', [
                'expanded' => true,
                'label' => 'Kön',
                'choices' => [
                    'M' => 'Man',
                    'F' => 'Kvinna',
                ]
            ])
            ->add('about', 'textarea', ['label' => 'Berätta om dig själv'])
            ->add('from', 'choice', [
                'label' => 'Vilket land kommer du ifrån?',
                'choices' => Countries::getList(),
            ])
            ->add('languages', 'choice', [
                'label' => 'Vilka språk talar du?',
                'choices' => Languages::getActiveList(),
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('profilePicture', 'hidden')
        ;
        $user = $builder->getData();
        if (!$user->hasRole('ROLE_COMPLETE_USER')) {
            $builder->add('city', 'entity', [
                'label' => 'Här vill jag fika',
                'class' => 'AppBundle:City',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
                'property' => 'name',
                'mapped' => false,
            ]);
        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User',
        ]);
    }

    public function getName()
    {
        return 'user';
    }
}
