<?php

namespace AppBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditConnectionRequestType extends ConnectionRequestType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
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
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
    }

    public function getName()
    {
        return 'edit_connection_request';
    }
}
