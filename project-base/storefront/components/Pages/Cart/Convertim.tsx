import { getGtm, mapCartData, mapPaymentsData, mapStoresData, mapTransportsData } from './convertimUtils';
import {
    ConvertimComponent,
    ConvertimOrderObject,
    GetCartType,
    GetPaymentsType,
    GetStoresType,
    GetTransportsType,
} from 'convertim-react-lib';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useRemoveCartMutation } from 'graphql/requests/cart/mutations/RemoveCartMutation.generated';
import { useTransportsWithPaymentsAndStoresForConvertimQuery } from 'graphql/requests/transports/queries/TransportsWithPaymentsAndStoresForConvertimQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useLogout } from 'utils/auth/useLogout';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

type ConvertimProps = { cart?: TypeCartFragment | null; convertimProjectUuid: string };

export const Convertim: FC<ConvertimProps> = ({ cart, convertimProjectUuid }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const [, removeCartMutation] = useRemoveCartMutation();
    const [{ data: transportsData, fetching: isTransportsFetching }] =
        useTransportsWithPaymentsAndStoresForConvertimQuery({
            variables: { cartUuid: cart?.uuid ?? null, displayInCartOnly: false },
        });
    const logout = useLogout();

    const dayNames = [
        t('Monday'),
        t('Tuesday'),
        t('Wednesday'),
        t('Thursday'),
        t('Friday'),
        t('Saturday'),
        t('Sunday'),
    ];

    const getCart = useCallback<GetCartType>((setData) => setData(mapCartData(cart, formatPrice)), [cart, formatPrice]);
    const getPayments = useCallback<GetPaymentsType>(
        (setData) => setData(mapPaymentsData(transportsData?.transports)),
        [transportsData],
    );
    const getStores = useCallback<GetStoresType>(
        (setData) => setData(mapStoresData(dayNames, cart, transportsData?.transports)),
        [dayNames, cart, transportsData],
    );
    const getTransports = useCallback<GetTransportsType>(
        (setData) => setData(mapTransportsData(transportsData?.transports, t)),
        [transportsData],
    );

    const handleEventsAfterOrderCreation = async () => {
        await removeCartMutation({ cartUuid: cart?.uuid ?? null });
        updateCartUuid(null);
    };

    if (isTransportsFetching) {
        return null;
    }

    return (
        <ConvertimComponent
            convertimUuid={convertimProjectUuid}
            getCart={getCart}
            getPayments={getPayments}
            getStores={getStores}
            getTransports={getTransports}
            gtm={getGtm()}
            isProduction={false}
            callbacks={{
                afterSaveOrder: (orderObject: ConvertimOrderObject, continueFunction) => {
                    // eslint-disable-next-line no-console
                    console.log('🚀 -> file: Convertim.tsx:94 -> orderObject', orderObject);
                    handleEventsAfterOrderCreation().then(() => continueFunction());
                },
                beforeOpenConvertim: (continueFunction) => {
                    continueFunction();
                },
                validateCustomZipTransport: (transportId: string, postalCode: string, setResult: () => void) => {
                    setResult();
                },
                afterLogout: () => {
                    logout();
                },
            }}
        />
    );
};
