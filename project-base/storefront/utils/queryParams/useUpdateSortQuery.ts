import { pushQueries } from './pushQueries';
import { useCurrentFilterQuery } from './useCurrentFilterQuery';
import { SEO_SENSITIVE_FILTERS, DEFAULT_SORT } from 'config/constants';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useRouter } from 'next/router';
import { useSessionStore } from 'store/useSessionStore';
import { UrlQueries } from 'types/urlQueries';
import { buildNewQueryAfterFilterChange } from 'utils/filterOptions/buildNewQueryAfterFilterChange';
import { getFilterWithoutEmpty } from 'utils/filterOptions/getFilterWithoutEmpty';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import {
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { useRedirectFromSeoCategory, getChangedDefaultFilters } from 'utils/seoCategories/queryParamsUtils';

export const useUpdateSortQuery = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const currentFilter = useCurrentFilterQuery();
    const defaultProductFiltersMap = useSessionStore((s) => s.defaultProductFiltersMap);
    const originalCategorySlug = useSessionStore((s) => s.originalCategorySlug);
    const redirectFromSeoCategory = useRedirectFromSeoCategory();

    const updateSortQuery = (sorting: TypeProductOrderingModeEnum) => {
        if (SEO_SENSITIVE_FILTERS.SORT && originalCategorySlug) {
            redirectFromSeoCategory(() => {
                const newQuery = buildNewQueryAfterFilterChange(
                    query,
                    getFilterWithoutEmpty(getChangedDefaultFilters(defaultProductFiltersMap, currentFilter)),
                    sorting,
                );

                pushQueries(router, newQuery, true, originalCategorySlug);
            });

            return;
        }

        pushQuerySort(sorting);
    };

    const pushQuerySort = (sorting: TypeProductOrderingModeEnum) => {
        const newQuery: UrlQueries = {
            ...query,
            [LOAD_MORE_QUERY_PARAMETER_NAME]: undefined,
            [PAGE_QUERY_PARAMETER_NAME]: undefined,
            [SORT_QUERY_PARAMETER_NAME]: sorting !== DEFAULT_SORT ? sorting : undefined,
        } as const;

        pushQueries(router, newQuery, true);
    };

    return updateSortQuery;
};
