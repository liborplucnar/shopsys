<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductPickerController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade $productListAdminFacade
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchProductFacade $advancedSearchProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly ProductListAdminFacade $productListAdminFacade,
        protected readonly AdvancedSearchProductFacade $advancedSearchProductFacade,
        protected readonly ProductFacade $productFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $jsInstanceId
     * @param bool $allowMainVariants
     * @param bool $allowVariants
     */
    #[Route(path: '/product-picker/pick-multiple/{jsInstanceId}/{allowMainVariants}/{allowVariants}')]
    public function pickMultipleAction(
        Request $request,
        $jsInstanceId,
        bool $allowMainVariants = true,
        bool $allowVariants = true,
    ) {
        return $this->getPickerResponse(
            $request,
            [
                'isMultiple' => true,
            ],
            [
                'isMultiple' => true,
                'jsInstanceId' => $jsInstanceId,
                'allowMainVariants' => $allowMainVariants,
                'allowVariants' => $allowVariants,
            ],
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $parentInstanceId
     */
    #[Route(path: '/product-picker/pick-single/{parentInstanceId}/', defaults: ['parentInstanceId' => '__instance_id__'])]
    public function pickSingleAction(Request $request, $parentInstanceId)
    {
        return $this->getPickerResponse(
            $request,
            [
                'isMultiple' => false,
            ],
            [
                'isMultiple' => false,
                'parentInstanceId' => $parentInstanceId,
                'allowMainVariants' => $request->query->getBoolean('allowMainVariants', true),
                'allowVariants' => $request->query->getBoolean('allowVariants', true),
            ],
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array $viewParameters
     * @param array $gridViewParameters
     */
    protected function getPickerResponse(Request $request, array $viewParameters, array $gridViewParameters)
    {
        $advancedSearchForm = $this->advancedSearchProductFacade->createAdvancedSearchForm($request);
        $advancedSearchData = $advancedSearchForm->getData();
        $quickSearchData = new QuickSearchFormData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, $quickSearchData);
        $quickSearchForm->handleRequest($request);

        $isAdvancedSearchFormSubmitted = $this->advancedSearchProductFacade->isAdvancedSearchFormSubmitted($request);

        if ($isAdvancedSearchFormSubmitted) {
            $queryBuilder = $this->advancedSearchProductFacade->getQueryBuilderByAdvancedSearchData(
                $advancedSearchData,
            );
        } else {
            $queryBuilder = $this->productListAdminFacade->getQueryBuilderByQuickSearchData($quickSearchData);
        }

        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'p.id',
            function ($row) {
                $product = $this->productFacade->getById($row['p']['id']);
                $row['product'] = $product;
                // actual visibility is rendered in the template, this is just a placeholder for column
                $row['visibility'] = null;

                return $row;
            },
        );

        $grid = $this->gridFactory->create('productPicker', $dataSource);
        $grid->enablePaging();
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'pt.name', t('Name'), true);
        $grid->addColumn('catnum', 'p.catnum', t('Catalog number'), true);
        $grid->addColumn('visibility', 'visibility', t('Visibility'))
            ->setClassAttribute('table-col table-col-10 text-center');
        $grid->addColumn('select', 'p.id', '')->setClassAttribute('table-col table-col-15 text-center');

        $gridViewParameters['VARIANT_TYPE_MAIN'] = Product::VARIANT_TYPE_MAIN;
        $gridViewParameters['VARIANT_TYPE_VARIANT'] = Product::VARIANT_TYPE_VARIANT;
        $grid->setTheme('@ShopsysFramework/Admin/Content/ProductPicker/listGrid.html.twig', $gridViewParameters);

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        $viewParameters['gridView'] = $grid->createView();
        $viewParameters['quickSearchForm'] = $quickSearchForm->createView();
        $viewParameters['advancedSearchForm'] = $advancedSearchForm->createView();
        $viewParameters['isAdvancedSearchFormSubmitted'] = $this->advancedSearchProductFacade->isAdvancedSearchFormSubmitted(
            $request,
        );

        return $this->render('@ShopsysFramework/Admin/Content/ProductPicker/list.html.twig', $viewParameters);
    }
}
