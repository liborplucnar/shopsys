import { STATIC_REWRITE_PATHS } from 'config/staticRewritePaths';
import { NextMiddleware, NextRequest, NextResponse } from 'next/server';
import {
    FriendlyPageTypesValue,
    FriendlyPagesDestinations,
    FriendlyPagesTypes,
    FriendlyPagesTypesKey,
} from 'types/friendlyUrl';
import { getDomainIdFromHostname } from 'utils/domain/getDomainIdFromHostname';

// eslint-disable-next-line @typescript-eslint/no-var-requires
const ERROR_PAGE_ROUTE = '/404';
const MIDDLEWARE_STATUS_CODE_KEY = 'middleware-status-code';
const MIDDLEWARE_STATUS_MESSAGE_KEY = 'middleware-status-message';

export const middleware: NextMiddleware = async (request) => {
    try {
        if (request.url.includes('_next/data')) {
            return new NextResponse(null, { status: 404 });
        }

        // redirect to HP when user is logged in
        const authProtectedPaths = ['/login', '/reset-password'];
        const accessToken = request.cookies.get('accessToken')?.value;

        if (accessToken && authProtectedPaths.some((path) => request.nextUrl.pathname.startsWith(path))) {
            return NextResponse.redirect(new URL('/app', request.url));
        }

        // reset auth tokens if they are invalid
        const response = await validateAuthTokens(request);

        const host = getHostFromRequest(request);
        const domainUrlFromStaticUrls = getDomainUrlFromStaticUrls(host);
        const staticUrlsAvailableForDomain = getStaticUrlsAvailableForDomain(domainUrlFromStaticUrls);
        const rewriteTargetUrl = getRewriteTargetPathname(request, staticUrlsAvailableForDomain);

        if (rewriteTargetUrl) {
            const rewriteUrlObject = new URL(rewriteTargetUrl, request.url);
            addQueryParametersToRewriteUrlObject(rewriteUrlObject, request.nextUrl.search);

            return NextResponse.rewrite(rewriteUrlObject, response);
        }

        const { search } = new URL(request.url);
        const queryParams = new URLSearchParams(search);
        const slugTypeQueryParam = queryParams.get('slugType');

        if (slugTypeQueryParam) {
            return rewriteDynamicPages(slugTypeQueryParam as FriendlyPageTypesValue, request.url, search);
        }

        const pageTypeResponse = await fetch(`${process.env.INTERNAL_ENDPOINT}resolve-friendly-url`, {
            method: 'POST',
            body: JSON.stringify({
                slug: request.nextUrl.pathname,
                domainId: getDomainIdFromHostname(request.headers.get('Host') as string),
            }),
        });

        if (!pageTypeResponse.ok) {
            const is400Error = isInRange(pageTypeResponse.status, 400, 499);
            const is500Error = isInRange(pageTypeResponse.status, 500, 599);

            let statusMessage = 'Unknown middleware error for ' + request.url;
            if (is400Error) {
                statusMessage = 'Friendly URL page not found for ' + request.url;
            } else if (is500Error) {
                statusMessage = 'Middleware runtime error for ' + request.url;
            }

            return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
                ...response,
                headers: [
                    [MIDDLEWARE_STATUS_CODE_KEY, pageTypeResponse.status.toString()],
                    [MIDDLEWARE_STATUS_MESSAGE_KEY, statusMessage],
                ],
            });
        }

        const pageTypeParsedResponse: { route: FriendlyPageTypesValue; redirectTo: string; redirectCode: number } =
            await pageTypeResponse.json();

        if (pageTypeParsedResponse.redirectTo && pageTypeParsedResponse.redirectTo !== request.url) {
            return NextResponse.redirect(
                new URL(
                    `${pageTypeParsedResponse.redirectTo}${queryParams.toString() !== '' ? `?${queryParams}` : ''}`,
                    request.url,
                ).href,
                {
                    ...response,
                    status: pageTypeParsedResponse.redirectCode,
                },
            );
        }

        return rewriteDynamicPages(pageTypeParsedResponse.route, request.url, search);
    } catch (e) {
        if (
            (process.env.ERROR_DEBUGGING_LEVEL === 'console' ||
                process.env.ERROR_DEBUGGING_LEVEL === 'toast-and-console') &&
            e instanceof Error
        ) {
            return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
                headers: [
                    [MIDDLEWARE_STATUS_CODE_KEY, '500'],
                    [MIDDLEWARE_STATUS_MESSAGE_KEY, e.message],
                ],
            });
        }

        return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
            headers: [
                [MIDDLEWARE_STATUS_CODE_KEY, '500'],
                [MIDDLEWARE_STATUS_MESSAGE_KEY, 'Middleware runtime error for ' + request.url],
            ],
        });
    }
};

export const config = {
    matcher: [
        '/((?!api|_next|favicon.ico|fonts|svg|images|locales|icons|grapesjs-template|grapesjs-homepage-article-template|grapesjs-article-template|robots).*)',
    ],
};

const isInRange = (number: number, start: number, end: number) => number >= start && start <= end;

const rewriteDynamicPages = (pageType: FriendlyPageTypesValue, rewriteUrl: string, queryParams: string) => {
    const pageTypeKey = (Object.keys(FriendlyPagesTypes) as FriendlyPagesTypesKey[]).find(
        (key) => FriendlyPagesTypes[key] === pageType,
    );

    const host = new URL(rewriteUrl).origin;
    if (pageTypeKey) {
        return NextResponse.rewrite(new URL(`${FriendlyPagesDestinations[pageTypeKey]}${queryParams}`, host));
    }

    return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, host), {
        headers: [
            [MIDDLEWARE_STATUS_CODE_KEY, '404'],
            [MIDDLEWARE_STATUS_MESSAGE_KEY, 'Friendly URL page not found for ' + rewriteUrl],
        ],
    });
};

const getHostFromRequest = (request: NextRequest): string => {
    const requestHeaders = new Headers(request.headers);
    const host = requestHeaders.get('host');

    if (host === null) {
        throw new Error(`Host was not found in the request header.`);
    }

    return host;
};

const getDomainUrlFromStaticUrls = (host: string): string => {
    const domainUrlFromStaticUrls = Object.keys(STATIC_REWRITE_PATHS).find((domainUrl) => domainUrl.match(host));

    if (domainUrlFromStaticUrls === undefined) {
        throw new Error(`Host ${host} does not have a corresponding URL in the available static URLS.`);
    }

    return domainUrlFromStaticUrls;
};

const getStaticUrlsAvailableForDomain = (domainUrlFromStaticUrls: string): Record<string, string> => {
    const staticUrlsAvailableForDomain = STATIC_REWRITE_PATHS[domainUrlFromStaticUrls];

    return staticUrlsAvailableForDomain;
};

const getRewriteTargetPathname = (
    request: NextRequest,
    staticUrlsAvailableForDomain: Record<string, string>,
): string => {
    let rewriteTargetPathnameArray: string[] = [];

    for (const [staticRewritePathname, staticLocalizedPathname] of Object.entries(staticUrlsAvailableForDomain)) {
        const requestedPathnameSegments = request.nextUrl.pathname.split('/');
        const staticRewritePathnameSegments = staticRewritePathname.split('/');
        const staticLocalizedPathnameSegments = staticLocalizedPathname.split('/');

        let areAllSegmentsIdenticalOrDynamic = true;
        const rewriteTargetPathnameArrayBuffer = [];
        const hasDynamicSegment = staticRewritePathnameSegments.some((segment) => isPathnameSegmentDynamic(segment));

        for (let index = 0; index < requestedPathnameSegments.length; index++) {
            const isCurrentPathnameSegmentDynamic = isPathnameSegmentDynamic(staticRewritePathnameSegments[index]);

            areAllSegmentsIdenticalOrDynamic =
                areAllSegmentsIdenticalOrDynamic &&
                (staticLocalizedPathnameSegments[index] === requestedPathnameSegments[index] ||
                    isCurrentPathnameSegmentDynamic);

            if (isCurrentPathnameSegmentDynamic) {
                rewriteTargetPathnameArrayBuffer.push(requestedPathnameSegments[index]);
            } else {
                rewriteTargetPathnameArrayBuffer.push(staticRewritePathnameSegments[index]);
            }
        }

        if (hasDynamicSegment && requestedPathnameSegments.length !== staticRewritePathnameSegments.length) {
            areAllSegmentsIdenticalOrDynamic = false;
        }

        if (areAllSegmentsIdenticalOrDynamic) {
            rewriteTargetPathnameArray = [...rewriteTargetPathnameArrayBuffer];
        }
    }

    const rewriteTargetPathname = rewriteTargetPathnameArray.join('/');

    return rewriteTargetPathname;
};

const addQueryParametersToRewriteUrlObject = (rewriteUrlObject: URL, originalUrlQueryParams: string) => {
    rewriteUrlObject.search = originalUrlQueryParams;
};

const isPathnameSegmentDynamic = (segment?: string) => segment?.charAt(0) === ':';

const validateAuthTokens = async (request: NextRequest) => {
    const response = NextResponse.next();
    const accessToken = request.cookies.get('accessToken')?.value;
    if (!accessToken) {
        return response;
    }

    try {
        // TODO: possibly replace this CurrentCustomerUserQuery with the `isAuthorized` query
        const currentUserResp = await gqlQueryFetch(getIsAuthenticatedBody(), accessToken);

        if (currentUserResp.status !== 401) {
            return response;
        }

        const refreshToken = request.cookies.get('refreshToken')?.value;
        if (!refreshToken) {
            deleteAuthTokensFromCookies(response);
            return response;
        }

        await refreshAuthTokensInCookies(response, refreshToken);
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error('Auth token validation error:', e);
    }

    return response;
};

const refreshAuthTokensInCookies = async (response: NextResponse, refreshToken: string) => {
    const refreshTokensReponse = await gqlQueryFetch(getRefreshTokensBody({ refreshToken }));

    if (!refreshTokensReponse.ok) {
        deleteAuthTokensFromCookies(response);
        return;
    }

    const { data } = await refreshTokensReponse.json();
    if (!data?.RefreshTokens) {
        deleteAuthTokensFromCookies(response);
        return;
    }

    const { accessToken: newAccessToken, refreshToken: newRrefreshToken } = data.RefreshTokens;
    response.cookies.set('accessToken', newAccessToken);
    response.cookies.set('refreshToken', newRrefreshToken);
    // eslint-disable-next-line no-console
    console.log('Tokens refreshed');
};

const gqlQueryFetch = (body: any, accessToken?: string) => {
    const defaultHeaders = {
        Accept: 'application/graphql-response+json, application/graphql+json, application/json, text/event-stream, multipart/mixed',
        Originalhost: '127.0.0.1:8000',
        'X-Forwarded-Proto': 'off',
        'Content-Type': 'application/json',
    };

    return fetch(`${process.env.INTERNAL_ENDPOINT}graphql/`, {
        headers: {
            ...defaultHeaders,
            ...(accessToken && { 'X-Auth-Token': `Bearer ${accessToken}` }),
        },
        body: JSON.stringify(body),
        method: 'POST',
    });
};

const getIsAuthenticatedBody = () => {
    return {
        operationName: 'OrdersQuery',
        query: 'query OrdersQuery($after: String, $first: Int) { orders(after: $after, first: $first) { totalCount } }',
        variables: { after: '', first: 28 },
    };
};

const getRefreshTokensBody = (variables = {}) => {
    return {
        operationName: 'RefreshTokens',
        query: 'mutation RefreshTokens($refreshToken: String!) { RefreshTokens(input: {refreshToken: $refreshToken}) { ...TokenFragments } } fragment TokenFragments on Token { accessToken refreshToken }',
        variables,
    };
};

const deleteAuthTokensFromCookies = (response: NextResponse) => {
    response.cookies.delete('accessToken');
    response.cookies.delete('refreshToken');
};
