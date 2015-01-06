<?php

namespace SS6\ShopBundle\Form\Admin\Product;

use SS6\ShopBundle\Component\Constraints\NotSelectedDomainToShow;
use SS6\ShopBundle\Component\Transformers\InverseArrayValuesTransformer;
use SS6\ShopBundle\Form\DatePickerType;
use SS6\ShopBundle\Form\YesNoType;
use SS6\ShopBundle\Model\Product\ProductData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class ProductFormType extends AbstractType {

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Vat\Vat[]
	 */
	private $vats;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Availability\Availability[]
	 */
	private $availabilities;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductDomainHiddenToShowTransformer
	 */
	private $inverseArrayValuesTransformer;

	/**
	 * @var \SS6\ShopBundle\Model\Department\Department[]
	 */
	private $departments;

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\Vat[] $vats
	 * @param \SS6\ShopBundle\Model\Product\Availability\Availability[] $availabilities
	 * @param \SS6\ShopBundle\Model\Product\ProductDomainHiddenToShowTransformer $inverseArrayValuesTransformer
	 * @param \SS6\ShopBundle\Model\Department\Department[] $departments
	 */
	public function __construct(
		array $vats,
		array $availabilities,
		InverseArrayValuesTransformer $inverseArrayValuesTransformer,
		array $departments
	) {
		$this->vats = $vats;
		$this->availabilities = $availabilities;
		$this->inverseArrayValuesTransformer = $inverseArrayValuesTransformer;
		$this->departments = $departments;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'product';
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', 'localized', array(
				'main_constraints' => array(
					new Constraints\NotBlank(array('message' => 'Prosím vyplňte název')),
				),
				'options' => array('required' => false),
			))
			->add(
				$builder
					->create('showOnDomains', 'domains', array(
						'constraints' => array(
							new NotSelectedDomainToShow(array('message' => 'Musíte vybrat alespoň jednu doménu')),
						),
						'property_path' => 'hiddenOnDomains'
					))
					->addViewTransformer($this->inverseArrayValuesTransformer)
			)
			->add('hidden', new YesNoType(), array('required' => false))
			->add('catnum', 'text', array(
				'required' => false,
				'constraints' => array(
					new Constraints\Length(array('max' => 100, 'maxMessage' => 'Katalogové číslo nesmí být delší než {{ limit }} znaků')),
				),
			))
			->add('partno', 'text', array(
				'required' => false,
				'constraints' => array(
					new Constraints\Length(array('max' => 100, 'maxMessage' => 'Výrobní číslo nesmí být delší než {{ limit }} znaků')),
				),
			))
			->add('ean', 'text', array(
				'required' => false,
				'constraints' => array(
					new Constraints\Length(array('max' => 100, 'maxMessage' => 'EAN nesmí být delší než {{ limit }} znaků')),
				),
			))
			->add('description', 'localized', array(
				'type' => 'ckeditor',
				'required' => false,
			))
			->add('price', 'money', array(
				'currency' => false,
				'precision' => 6,
				'required' => true,
				'invalid_message' => 'Prosím zadejte cenu v platném formátu (kladné číslo s desetinnou čárkou nebo tečkou)',
				'constraints' => array(
					new Constraints\NotBlank(array('message' => 'Prosím vyplňte cenu')),
					new Constraints\GreaterThanOrEqual(array(
						'value' => 0,
						'message' => 'Cena musí být větší nebo rovna {{ compared_value }}'
					)),
				),
			))
			->add('vat', 'choice', array(
				'required' => true,
				'choice_list' => new ObjectChoiceList($this->vats, 'name', array(), null, 'id'),
				'constraints' => array(
					new Constraints\NotBlank(array('message' => 'Prosím vyplňte výši DPH')),
				),
			))
			->add('sellingFrom', new DatePickerType(), array(
				'required' => false,
				'constraints' => array(
					new Constraints\Date(array('message' => 'Datum zadávejte ve formátu dd.mm.rrrr')),
				),
				'invalid_message' => 'Datum zadávejte ve formátu dd.mm.rrrr',
			))
			->add('sellingTo', new DatePickerType(), array(
				'required' => false,
				'constraints' => array(
					new Constraints\Date(array('message' => 'Datum zadávejte ve formátu dd.mm.rrrr')),
				),
				'invalid_message' => 'Datum zadávejte ve formátu dd.mm.rrrr',
			))
			->add('stockQuantity', 'integer', array(
				'required' => false,
				'invalid_message' => 'Prosím zadejte číslo',
			))
			->add('availability', 'choice', array(
				'required' => false,
				'choice_list' => new ObjectChoiceList($this->availabilities, 'name', array(), null, 'id'),
			))
			->add('departments', 'choice', array(
				'required' => false,
				'choice_list' => new ObjectChoiceList($this->departments, 'name', array(), null, 'id'),
				'multiple' => true,
				'expanded' => true,
			));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'data_class' => ProductData::class,
			'attr' => array('novalidate' => 'novalidate'),
		));
	}

}
