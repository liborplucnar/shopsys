<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Pricing\Currency;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class CurrencySettingsFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(private readonly CurrencyFacade $currencyFacade)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('defaultCurrency', ChoiceType::class, [
                'required' => true,
                'choices' => $this->currencyFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter default currency']),
                ],
            ])
            ->add('domainDefaultCurrencies', CollectionType::class, [
                'required' => true,
                'entry_type' => ChoiceType::class,
                'entry_options' => [
                    'required' => true,
                    'choices' => $this->currencyFacade->getAll(),
                    'choice_label' => 'name',
                    'choice_value' => 'id',
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter default currency']),
                    ],
                ],
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
