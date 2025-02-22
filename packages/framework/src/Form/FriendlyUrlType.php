<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class FriendlyUrlType extends AbstractType
{
    protected const string SLUG_REGEX = '/^[\w_\-\/]+$/';

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(UrlListData::FIELD_DOMAIN, DomainType::class, [
            'displayUrl' => true,
            'required' => true,
        ]);
        $builder->add(UrlListData::FIELD_SLUG, TextType::class, [
            'required' => true,
            'constraints' => [
                new Constraints\NotBlank(),
                new Constraints\Regex(static::SLUG_REGEX),
            ],
        ]);
    }
}
