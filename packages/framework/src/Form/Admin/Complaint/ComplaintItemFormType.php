<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Complaint;

use Shopsys\FrameworkBundle\Form\BasicFileUploadType;
use Shopsys\FrameworkBundle\Form\FileUploadType;
use Shopsys\FrameworkBundle\Form\MultiLocaleBasicFileUploadType;
use Shopsys\FrameworkBundle\Form\MultiLocaleFileUploadType;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ComplaintItemFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                ],
                'error_bubbling' => true,
            ])
            ->add('quantity', IntegerType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter quantity']),
                    new Constraints\GreaterThan(
                        ['value' => 0, 'message' => 'Quantity must be greater than {{ compared_value }}'],
                    ),
                ],
                'error_bubbling' => true,
            ])
            ->add('files', FileUploadType::class, [
                'required' => false,
                'file_entity_class' => ComplaintItem::class,
                'file_constraints' => [
                    new Constraints\File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded file is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an file is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'entity' => $options['complaintItem'],
                'label' => t('Files'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ComplaintItemData::class,
                'attr' => ['novalidate' => 'novalidate'],

            ])
            ->setRequired('complaintItem')
            ->setAllowedTypes('complaintItem', [ComplaintItem::class, 'null']);
    }
}
