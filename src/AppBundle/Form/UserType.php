<?php

namespace AppBundle\Form;

use AppBundle\Entity\ConnectionRequest;
use AppBundle\Entity\User;
use AppBundle\Enum\FriendTypes;
use AppBundle\Enum\OccupationTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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

        $categories = [];
        $musicCategories = [];
        foreach ($query->getResult() as $category) {
            if (get_class($category) == 'AppBundle\Entity\GeneralCategory') {
                $categories[] = $category;
            } elseif (get_class($category) == 'AppBundle\Entity\MusicCategory') {
                $musicCategories[] = $category;
            }
        };

        /** @var User $user */
        $user = $builder->getData();

        $builder
            ->add('firstName', 'text', ['label' => 'user.form.first_name'])
            ->add('lastName', 'text', ['label' => 'user.form.last_name'])
            ->add('wantToLearn', 'boolean_choice', [
                'expanded' => true,
                'label' => 'user.form.want_to_learn',
                'choices' => [
                    'user.form.want_to_learn.choice.learn'  => '1',
                    'user.form.want_to_learn.choice.teach'  => '0'
                ],
                'choices_as_values' => true,
                'data' => $user->hasRole('ROLE_COMPLETE_USER') ? $user->getWantToLearn() : null,
            ])
            ->add('categories', 'entity', [
                    'class' => 'AppBundle:GeneralCategory',
                    'multiple' => true,
                    'expanded' => true,
                    'choice_list' => new ArrayChoiceList($categories),
                    'property' => 'name',
                    'label' => 'user.form.categories',
                ]
            )
            ->add('musicCategories', 'entity', [
                    'class' => 'AppBundle:MusicCategory',
                    'multiple' => true,
                    'expanded' => true,
                    'choice_list' => new ArrayChoiceList($musicCategories),
                    'property' => 'name',
                ]
            )
            ->add('age', 'choice', [
                'label' => 'user.form.age',
                'empty_data'  => null,
                'required'    => false,
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
                'choices' => Countries::getList($options['locale']),
                'empty_data' => null,
                'empty_value' => ''
            ])
            // Might be removed after music friend campaign
            // ->add('district', 'text', ['label' => 'user.form.district'])
            ->add('hasChildren', 'boolean_choice', [
                'expanded' => true,
                'label' => 'user.form.has_children',
                'choices' => [
                    'no',
                    'yes',
                ],
                'data' => $user->hasRole('ROLE_COMPLETE_USER') ? $user->hasChildren() : null,
            ])
            ->add('profilePicture', 'hidden')
            ->add('type', 'choice', [
                'expanded' => true,
                'multiple' => false,
                'label' => 'user.form.fikatype',
                'choices' => FriendTypes::listTypesWithTranslationKeys(),
            ])
            ->add('municipality', 'entity', [
                    'class' => 'AppBundle:Municipality',
                    'property' => 'name',
                    'empty_data'  => null,
                    'required' => false,
                    'label' => 'user.form.municipality',
                ]
            )
            ->add('activities', 'textarea', [
                'label' => 'user.form.activities',
            ])
            ->add('occupation', 'choice', [
                'label' => 'user.form.occupation',
                'choices' => OccupationTypes::listTypesWithTranslationKeys(),
                'empty_data' => null,
                'required' => false
            ])
            ->add('occupationDescription', 'textarea', [
                'label_attr' => ['id' => 'occupationDescriptionLabel'],
                'required' => false,
            ])
            ->add('education', 'boolean_choice', [
                'expanded' => true,
                'label' => 'user.form.has_education',
                'choices' => [
                    'no',
                    'yes'
                ],
                'data' => $user->hasRole('ROLE_COMPLETE_USER') ? $user->hasEducation() : null,
            ])
            ->add('educationDescription', 'textarea', [
                'label' => 'user.form.education_description',
                'required' => false,
            ])
            ->add('timeInSweden', 'textarea', [
                'label' => 'user.form.time_in_sweden',
            ])
            ->add('childrenAge', 'textarea', [
                'label' => 'user.form.children_age',
                'required' => false,
            ])
            ->add('aboutMusic', 'textarea', [
                'label' => 'user.form.about_music',
                'required' => false,
            ])
            ->add('canSing', 'boolean_choice', [
                'expanded' => true,
                'label' => 'user.form.can_sing',
                'choices' => [
                    'no',
                    'yes'
                ],
                'data' => $user->hasRole('ROLE_COMPLETE_USER') ? $user->isCanSing() : null,
            ])
            ->add('canPlayInstrument', 'boolean_choice', [
                'expanded' => true,
                'label' => 'user.form.can_play_instrument',
                'choices' => [
                    'no',
                    'yes'
                ],
                'data' => $user->hasRole('ROLE_COMPLETE_USER') ? $user->isCanPlayInstrument() : null,
            ])
            ->add('aboutInstrument', 'textarea', [
                'label' => 'user.form.about_instrument',
                'required' => false,
            ])
            ->add('professionalMusician', 'boolean_choice', [
                'expanded' => true,
                'label' => 'user.form.professional_musician',
                'choices' => [
                    'user.form.professional_musician.no',
                    'user.form.professional_musician.yes',
                ],
                'data' => $user->hasRole('ROLE_COMPLETE_USER') ? $user->isProfessionalMusician() : null,
            ])
            ->add('musicGenre', 'textarea', [
                'label' => 'user.form.music_genre',
                'required' => false,
            ])
            ->add('phoneNumber', 'text', [
                'label' => 'user.form.phone_number',
                'required' => false,
                'attr' => ['placeholder' => '0701234567'],
            ])
            ->add('languages', 'text', [
                'label' => 'user.form.languages',
                'required' => false,
            ])
        ;

        if (!$user->hasRole('ROLE_COMPLETE_USER') || $options['add_connection_request']) {
            $builder->add('connectionRequests',
                'collection',
                [
                    'type' => 'connection_request',
                    'allow_add' => true,
                    'by_reference' => false,
                    'options' => [
                        'remove_type' => true,
                        'remove_want_to_learn' => true,
                    ],
                ]
            );

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var User $user */
                $user = $event->getData();
                $user->addConnectionRequest(new ConnectionRequest());
            });

            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                /** @var User $user */
                $user = $event->getForm()->getNormData();
                $connectionRequest = $user->getConnectionRequests()->last();
                $connectionRequest->setType($user->getType());
                $connectionRequest->setWantToLearn($user->getWantToLearn());

                if ($connectionRequest->getType() == FriendTypes::START) {
                    $connectionRequest->setCity(null);
                } else {
                    $connectionRequest->setMunicipality(null);
                }
            });
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User',
            'add_connection_request' => false,
        ]);
        $resolver->setRequired([
            'manager',
            'locale'
        ]);
    }

    public function getName()
    {
        return 'user';
    }
}
