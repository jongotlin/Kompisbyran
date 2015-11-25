<?php

namespace AppBundle\Form;

use AppBundle\Enum\Languages;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Enum\Countries;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Doctrine\Orm\EntityManager $manager */
        $manager = $options['manager'];
        $query = $manager->createQuery('SELECT c FROM AppBundle:Category c ORDER BY c.name');
        $query->setHint(
            \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );
        $query->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $options['locale']);

        $categories = $query->getResult();

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
                    'choice_list' => new ArrayChoiceList($categories),
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
                    'X' => 'Vill inte ange',
                ]
            ])
            ->add('about', 'textarea', ['label' => 'Berätta om dig själv'])
            ->add('from', 'choice', [
                'label' => 'Vilket land kommer du ifrån?',
                'choices' => Countries::getList(),
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
        $resolver->setRequired([
            'manager',
            'locale',
        ]);
    }

    public function getName()
    {
        return 'user';
    }
}
