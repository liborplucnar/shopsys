import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeOrderDetailItemFragment = { __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, order: { __typename?: 'Order', uuid: string, number: string, creationDate: any }, product: { __typename?: 'MainVariant', slug: string, isVisible: boolean, isSellingDenied: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'RegularProduct', slug: string, isVisible: boolean, isSellingDenied: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'Variant', slug: string, isVisible: boolean, isSellingDenied: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null };


      export interface PossibleTypesResultData {
        possibleTypes: {
          [key: string]: string[]
        }
      }
      const result: PossibleTypesResultData = {
  "possibleTypes": {
    "Advert": [
      "AdvertCode",
      "AdvertImage"
    ],
    "ArticleInterface": [
      "ArticleSite",
      "BlogArticle"
    ],
    "Breadcrumb": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ],
    "CustomerUser": [
      "CompanyCustomerUser",
      "RegularCustomerUser"
    ],
    "Hreflang": [
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "SeoPage",
      "Variant"
    ],
    "NotBlogArticleInterface": [
      "ArticleLink",
      "ArticleSite"
    ],
    "ParameterFilterOptionInterface": [
      "ParameterCheckboxFilterOption",
      "ParameterColorFilterOption",
      "ParameterSliderFilterOption"
    ],
    "Product": [
      "MainVariant",
      "RegularProduct",
      "Variant"
    ],
    "ProductListable": [
      "Brand",
      "Category",
      "Flag"
    ],
    "Slug": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ]
  }
};
      export default result;
    
export const OrderDetailItemFragment = gql`
    fragment OrderDetailItemFragment on OrderItem {
  __typename
  uuid
  name
  unitPrice {
    ...PriceFragment
  }
  totalPrice {
    ...PriceFragment
  }
  vatRate
  quantity
  unit
  type
  order {
    uuid
    number
    creationDate
  }
  product {
    slug
    isVisible
    isSellingDenied
    mainImage {
      ...ImageFragment
    }
  }
}
    ${PriceFragment}
${ImageFragment}`;