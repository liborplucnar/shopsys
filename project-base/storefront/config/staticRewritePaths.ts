import getConfig from 'next/config';

const nextConfig = getConfig();

export const STATIC_REWRITE_PATHS = {
    [(nextConfig?.publicRuntimeConfig?.domains?.[0]?.url || process.env.NEXT_PUBLIC_DOMAIN_HOSTNAME_1) as string]: {
        '/about': '/about',
        '/app': '/app',
        '/search': '/search',
        '/cart': '/cart',
        '/contact': '/contact',
        '/order/transport-and-payment': '/order/transport-and-payment',
        '/order/contact-information': '/order/contact-information',
        '/reset-password': '/reset-password',
        '/order-confirmation': '/order-confirmation',
        '/stores': '/stores',
        '/brands-overview': '/brands-overview',
        '/login': '/login',
        '/customer': '/customer',
        '/customer/edit-profile': '/customer/edit-profile',
        '/customer/change-password': '/customer/change-password',
        '/customer/orders': '/customer/orders',
        '/customer/order-detail': '/customer/order-detail',
        '/customer/complaints': '/customer/complaints',
        '/customer/new-complaint': '/customer/new-complaint',
        '/customer/complaint-detail': '/customer/complaint-detail',
        '/customer/users': '/customer/users',
        '/registration': '/registration',
        '/new-password': '/new-password',
        '/personal-data-overview': '/personal-data-overview',
        '/personal-data-overview/:hash': '/personal-data-overview/:hash',
        '/personal-data-export': '/personal-data-export',
        '/order-payment-confirmation': '/order-payment-confirmation',
        '/order/payment-status-notify': '/order/payment-status-notify',
        '/order-detail/:urlHash': '/order-detail/:urlHash',
        '/user-consent': '/user-consent',
        '/abandoned-cart/:cartUuid': '/abandoned-cart/:cartUuid',
        '/grapesjs-template': '/grapesjs-template',
        '/product-comparison': '/product-comparison',
        '/wishlist': '/wishlist',
        '/styleguide': '/styleguide',
        '/social-login': '/social-login',
        '/_feedback': '/_feedback',
    },
    [(nextConfig?.publicRuntimeConfig?.domains?.[1]?.url || process.env.NEXT_PUBLIC_DOMAIN_HOSTNAME_2) as string]: {
        '/about': '/about',
        '/app': '/app',
        '/search': '/hledani',
        '/cart': '/kosik',
        '/contact': '/kontakt',
        '/order/transport-and-payment': '/objednavka/doprava-a-platba',
        '/order/contact-information': '/objednavka/kontaktni-udaje',
        '/reset-password': '/zapomenute-heslo',
        '/order-confirmation': '/potvrzeni-objednavky',
        '/stores': '/obchodni-domy',
        '/brands-overview': '/prehled-znacek',
        '/login': '/prihlaseni',
        '/customer': '/zakaznik',
        '/customer/edit-profile': '/zakaznik/upravit-udaje',
        '/customer/change-password': '/zakaznik/zmenit-heslo',
        '/customer/orders': '/zakaznik/objednavky',
        '/customer/order-detail': '/zakaznik/detail-objednavky',
        '/customer/complaints': '/zakaznik/reklamace',
        '/customer/new-complaint': '/zakaznik/nova-reklamace',
        '/customer/complaint-detail': '/zakaznik/detail-reklamace',
        '/customer/users': '/zakaznik/uzivatele',
        '/registration': '/registrace',
        '/new-password': '/nove-heslo',
        '/personal-data-overview': '/prehled-osobnich-udaju',
        '/personal-data-overview/:hash': '/prehled-osobnich-udaju/:hash',
        '/personal-data-export': '/export-osobnich-udaju',
        '/order-payment-confirmation': '/potvrzeni-platby-objednavky',
        '/order/payment-status-notify': '/order/payment-status-notify',
        '/order-detail/:urlHash': '/detail-objednavky/:urlHash',
        '/user-consent': '/uzivatelsky-souhlas',
        '/abandoned-cart/:cartUuid': '/opusteny-kosik/:cartUuid',
        '/grapesjs-template': '/grapesjs-template',
        '/product-comparison': '/porovnani-produktu',
        '/wishlist': '/oblibene-produkty',
        '/styleguide': '/styleguide',
        '/social-login': '/social-login',
        '/_feedback': '/_feedback',
    },
} as const;

export type StaticRewritePathKeyType = keyof (typeof STATIC_REWRITE_PATHS)[string];
