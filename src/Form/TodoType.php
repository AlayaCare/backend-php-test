<?php
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TodoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
        'constraints' => new Assert\NotBlank(),
    ))
            ->add('description', TextareaType::class, array(
        'constraints' => new Assert\NotBlank(),
        'attr' => array('rows' => '5'),

    ))
            ->add('status', ChoiceType::class, array(
        'choices'  => array(
            'New' => 'New',
            'In Progress' => 'In Progress',
            'Cancelled' => 'Cancelled',
            'Completed' => 'Completed',

        ),
        'choices_as_values' => true,
    ))
            ->add('submit', SubmitType::class, array(
        'label' => 'Save Todo',
        'attr'  => array(
            'class' => 'btn btn-success',
        ),
    ));

    }

}
