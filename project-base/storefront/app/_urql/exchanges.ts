import { getAuthExchangeOptions } from './authExchange';
import { cache } from './cache/cacheExchange';
import { getErrorExchange } from './errorExchange';
import { devtoolsExchange } from '@urql/devtools';
import { authExchange } from '@urql/exchange-auth';
import { Translate } from 'types/translation';
import { ClientOptions, fetchExchange, SSRExchange } from 'urql';
import { dedupExchange } from 'urql/dedupExchange';
import { operationNameExchange } from 'urql/operationNameExchange';

export const getUrqlExchanges = (ssrExchange: SSRExchange, t: Translate): ClientOptions['exchanges'] => [
    devtoolsExchange,
    dedupExchange,
    cache,
    getErrorExchange(t),
    ssrExchange,
    authExchange(getAuthExchangeOptions()),
    operationNameExchange,
    fetchExchange,
];
