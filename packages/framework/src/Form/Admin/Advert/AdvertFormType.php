<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Advert;

use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\CategoriesType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertData;
use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade;
use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class AdvertFormType extends AbstractType
{
    public const string VALIDATION_GROUP_TYPE_IMAGE = 'typeImage';
    public const string VALIDATION_GROUP_TYPE_CODE = 'typeCode';
    public const string SCENARIO_CREATE = 'create';
    public const string SCENARIO_EDIT = 'edit';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry $advertPositionRegistry
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        private readonly AdvertPositionRegistry $advertPositionRegistry,
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => t('Settings'),
        ]);

        $builderSettingsGroup
            ->add('domain', DisplayOnlyType::class, [
                'data' => $this->adminDomainTabsFacade->getSelectedDomainConfig()->getName(),
                'label' => t('Domain'),
            ]);

        if ($options['scenario'] === self::SCENARIO_EDIT) {
            $builderSettingsGroup
                ->add('id', DisplayOnlyType::class, [
                    'data' => $options['advert']->getId(),
                    'label' => t('ID'),
                ]);
        }

        $builderSettingsGroup
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name of advertisement area']),
                ],
                'label' => t('Name'),
                'attr' => [
                    'icon' => true,
                    'iconTitle' => t('Name serves only for internal use within the administration.'),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    t('HTML code') => Advert::TYPE_CODE,
                    t('Image with link') => Advert::TYPE_IMAGE,
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please choose advertisement type']),
                ],
                'label' => t('Type'),
            ])
            ->add('positionName', ChoiceType::class, [
                'required' => true,
                'choices' => array_flip($this->advertPositionRegistry->getAllLabelsIndexedByNames()),
                'placeholder' => t('-- Choose area --'),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please choose advertisement area']),
                ],
                'label' => t('Area'),
            ])
            ->add('categories', CategoriesType::class, [
                'required' => false,
                'domain_id' => $this->adminDomainTabsFacade->getSelectedDomainId(),
                'label' => t('Assign to category'),
                'display_as_row' => true,
            ])
            ->add('hidden', YesNoType::class, [
                'required' => false,
                'label' => t('Hide advertisement'),
            ])
            ->add('code', TextareaType::class, [
                'label' => t('Code'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter HTML code for advertisement area',
                        'groups' => [static::VALIDATION_GROUP_TYPE_CODE],
                    ]),
                ],
                'attr' => [
                    'class' => 'height-150',
                ],
                'js_container' => [
                    'container_class' => 'js-advert-type-content form-line__js',
                    'data_type' => 'code',
                ],
            ]);

        $builderImageGroup = $builder->create('image_group', GroupType::class, [
            'label' => t('Images'),
            'js_container' => [
                'container_class' => 'js-advert-type-content wrap-divider--top',
                'data_type' => 'image',
            ],
        ]);

        $builderImageGroup
            ->add('link', TextType::class, [
                'required' => false,
                'label' => t('Link'),
            ]);

        $imageConstraints = [
            new Constraints\NotBlank([
                'message' => 'Choose image',
                'groups' => [self::VALIDATION_GROUP_TYPE_IMAGE],
            ]),
        ];

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => Advert::class,
                'image_type' => AdvertFacade::IMAGE_TYPE_WEB,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '15M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'constraints' => ($options['web_image_exists'] ? [] : $imageConstraints),
                'label' => t('Upload new image'),
                'entity' => $options['advert'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        $builderImageGroup
            ->add('mobileImage', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => Advert::class,
                'image_type' => AdvertFacade::IMAGE_TYPE_MOBILE,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '15M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'constraints' => ($options['mobile_image_exists'] ? [] : $imageConstraints),
                'label' => t('Upload image for mobile devices'),
                'entity' => $options['advert'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add($builderImageGroup)
            ->add('datetimeVisibleFrom', DatePickerType::class, [
                'required' => false,
                'label' => t('Display date FROM'),
            ])
            ->add('datetimeVisibleTo', DatePickerType::class, [
                'required' => false,
                'label' => t('Display date TO'),
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['scenario', 'advert', 'web_image_exists', 'mobile_image_exists'])
            ->setAllowedTypes('web_image_exists', 'bool')
            ->setAllowedTypes('mobile_image_exists', 'bool')
            ->setAllowedValues('scenario', [self::SCENARIO_CREATE, self::SCENARIO_EDIT])
            ->setAllowedTypes('advert', [Advert::class, 'null'])
            ->setDefaults([
                'web_image_exists' => false,
                'mobile_image_exists' => false,
                'data_class' => AdvertData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advertData */
                    $advertData = $form->getData();

                    if ($advertData->type === Advert::TYPE_CODE) {
                        $validationGroups[] = static::VALIDATION_GROUP_TYPE_CODE;
                    } elseif ($advertData->type === Advert::TYPE_IMAGE) {
                        $validationGroups[] = static::VALIDATION_GROUP_TYPE_IMAGE;
                    }

                    return $validationGroups;
                },
            ]);
    }
}
