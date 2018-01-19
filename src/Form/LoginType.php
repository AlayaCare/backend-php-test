<?php       
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LoginType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', TextType::class , array(

            'label' => 'Username',
            'attr' => array(
                'class' => 'form-control',
                'placeholder' => 'myusername'
            ) ,
            'constraints' => array(
                new Assert\NotBlank()
            )
        ))->add('_password', PasswordType::class , array(

            'label' => 'Password',
            'attr' => array(
                'class' => 'form-control',
                'placeholder' => 'mysecret'
            ) ,
            'constraints' => array(
                new Assert\NotBlank()
            )
        ))->add('_target_path', HiddenType::class , array(

            'attr' => array(
                'value' => '/todo'
            )

        ))->add('submit', SubmitType::class , array(
            'attr' => array(
                'class' => 'btn btn-primary pull-right'
            )
        ));

    }

    /**
     * Set the form name
     * As of Symfony 2.8, the name defaults to the fully-qualified class name
     * 
     * @return null|string
     */
    public function getName()
    {

        return null;
    }

}


