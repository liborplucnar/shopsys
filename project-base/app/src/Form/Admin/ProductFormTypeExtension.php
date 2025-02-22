<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper $formBuilderHelper
     */
    public function __construct(
        private readonly FormBuilderHelper $formBuilderHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \App\Model\Product\Product|null $product */
        $product = $options['product'];

        $this->setSeoGroup($builder);
        $this->setPricesGroup($builder, $product);
        $this->setRelatedProductsGroup($builder, $product);
        $this->setVideoGroup($builder);

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Product\Product|null $product
     */
    private function setPricesGroup(FormBuilderInterface $builder, ?Product $product): void
    {
        $builderPricesGroup = $builder->get('pricesGroup');

        if ($this->isProductMainVariant($product)) {
            $builderPricesGroup->remove('disabledPricesOnMainVariant');
        }
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function setSeoGroup(FormBuilderInterface $builder): void
    {
        $builderSeoGroup = $builder->get('seoGroup');

        $builderSeoGroup->remove('seoH1s');
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function setVideoGroup(FormBuilderInterface $builder)
    {
        $videosGroup = $builder->create('videosGroup', GroupType::class, [
            'label' => t('Videos'),
        ]);
        $videosGroup
            ->add(
                $builder->create('productVideosData', CollectionType::class, [
                    'entry_type' => VideoTokenType::class,
                    'render_form_row' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'label' => false,
                    'required' => false,
                ]),
            );

        $builder->add($videosGroup);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Product\Product|null $product
     */
    private function setRelatedProductsGroup(FormBuilderInterface $builder, ?Product $product): void
    {
        if (!($product !== null && $product->isVariant())) {
            $relatedProductsGroupBuilder = $builder
                ->create('relatedProducts', ProductsType::class, [
                    'required' => false,
                    'main_product' => $product,
                    'label' => t('Related products'),
                    'allow_variants' => false,
                ]);

            $builder->add($relatedProductsGroupBuilder);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield ProductFormType::class;
    }

    /**
     * @param \App\Model\Product\Product|null $product
     * @return bool
     */
    private function isProductMainVariant(?Product $product): bool
    {
        return $product !== null && $product->isMainVariant();
    }
}
