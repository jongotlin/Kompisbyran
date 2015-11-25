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
            ->add('firstName', 'text', ['label' => 'user.form.first_name'])
            ->add('lastName', 'text', ['label' => 'user.form.last_name'])
            ->add('wantToLearn', 'choice', [
                'expanded' => true,
                'label' => 'user.form.want_to_learn',
                'choices' => [
                    true => 'user.form.want_to_learn.choice.learn',
                    false => 'user.form.want_to_learn.choice.teach',
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
                    'label' => 'user.form.categories',
                ]
            )
            ->add('age', 'choice', [
                'label' => 'user.form.age',
                'choices' => array_combine(range(18, 100), range(18, 100)),
            ])
            ->add('gender', 'choice', [
                'expanded' => true,
                'label' => 'user.form.gender',
                'choices' => [
                    'M' => 'user.form.gender.m',
                    'F' => 'user.form.gender.f',
                    'X' => 'user.form.gender.x',
                ]
            ])
            ->add('about', 'textarea', ['label' => 'user.form.about'])
            ->add('from', 'choice', [
                'label' => 'user.form.from',
                'choices' => Countries::getList(),
            ])
            ->add('district', 'text', ['label' => 'user.form.district'])
            ->add('profilePicture', 'hidden')
        ;
        $user = $builder->getData();
        if (!$user->hasRole('ROLE_COMPLETE_USER')) {
            $builder->add('city', 'entity', [
                'label' => 'user.form.city',
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
