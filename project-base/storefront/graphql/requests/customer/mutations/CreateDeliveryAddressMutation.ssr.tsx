import * as Types from '../../../types';

import gql from 'graphql-tag';
import { DeliveryAddressFragment } from '../fragments/DeliveryAddressFragment.ssr';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCreateDeliveryAddressMutationVariables = Types.Exact<{
  input: Types.TypeDeliveryAddressInput;
}>;


export type TypeCreateDeliveryAddressMutation = { __typename?: 'Mutation', CreateDeliveryAddress: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }> };


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
    

export const CreateDeliveryAddressMutationDocument = gql`
    mutation CreateDeliveryAddressMutation($input: DeliveryAddressInput!) {
  CreateDeliveryAddress(input: $input) {
    ...DeliveryAddressFragment
  }
}
    ${DeliveryAddressFragment}`;