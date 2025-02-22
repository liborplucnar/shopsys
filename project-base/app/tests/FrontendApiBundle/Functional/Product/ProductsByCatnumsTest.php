<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Shopsys\FrameworkBundle\Component\Translation\Translator;

class ProductsByCatnumsTest extends ProductsGraphQlTestCase
{
    public function testProductsByCatnums(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/productsByCatnums.graphql',
            [
                'catnums' => [
                    '9177759',
                    '532564',
                    'non-existing', // non-existing product – should be ignored
                    '9176544M', // main variant – should be present in the result
                    '5964035', // non-visible product – should be ignored
                    '9176522', // variant product – should be present in the result
                ],
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'productsByCatnums');

        $productsExpected = [
            [
                'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'catalogNumber' => '9177759',
            ], [
                'name' => t('Canon EH-22L', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'catalogNumber' => '532564',
            ], [
                'name' => t('Television Philips [M]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'catalogNumber' => '9176544M',
            ], [
                'name' => t('24" Philips [V]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'catalogNumber' => '9176522',
            ],
        ];

        $this->assertSameSize($productsExpected, $data);

        $this->assertSame($productsExpected, $data);
    }
}
