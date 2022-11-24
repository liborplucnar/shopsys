<?php

namespace Shopsys\FrameworkBundle\Component\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimedFormTypeExtension extends AbstractTypeExtension
{
    public const MINIMUM_FORM_FILLING_SECONDS = 5;
    public const OPTION_ENABLED = 'timed_spam_enabled';
    public const OPTION_MINIMUM_SECONDS = 'timed_spam_minimum_seconds';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Form\FormTimeProvider
     */
    protected $formTimeProvider;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Form\FormTimeProvider $formTimeProvider
     */
    public function __construct(FormTimeProvider $formTimeProvider)
    {
        $this->formTimeProvider = $formTimeProvider;
    }

    /**
     * {@inheritdoc}
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options[self::OPTION_ENABLED]) {
            return;
        }

        $builder->addEventSubscriber(new TimedSpamValidationListener(
            $this->formTimeProvider,
            $options
        ));
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array<string, mixed> $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options[self::OPTION_ENABLED] && !$view->parent && $options['compound']) {
            $this->formTimeProvider->generateFormTime($form->getName());
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::OPTION_ENABLED => false,
            self::OPTION_MINIMUM_SECONDS => self::MINIMUM_FORM_FILLING_SECONDS,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @return iterable<class-string>
     */
    public static function getExtendedTypes(): iterable
    {
        yield FormType::class;
    }
}
