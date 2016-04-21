<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation\FormType;

/**
 * @FormType
 */
class ConnectionRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('city', 'entity', [
                    'label' => 'connection_request.form.city',
                    'class' => 'AppBundle:City',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                    },
                    'property' => 'name',
                    'empty_value' => ''
                ]
            )
            ->add('comment', 'textarea', [
                'required' => false,
                'label' => 'connection_request.form.comment',
            ])
            ->add('musicFriend', 'checkbox', [
                'required' => false,
                'label' => 'Musikkompis',
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\ConnectionRequest',
        ]);
    }

    public function getName()
    {
        return 'connectionRequest';
    }
}
