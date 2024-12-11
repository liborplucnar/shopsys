import { InfoIconInCircle } from 'components/Basic/Icon/InfoIconInCircle';
import useTranslation from 'next-translate/useTranslation';
import { PaymentTypeEnum } from 'types/payment';
import { twMergeCustom } from 'utils/twMerge';

type OrderPaymentStatusBarProps = {
    orderPaymentType: string;
    orderIsPaid: boolean;
    orderHasPaymentInProcess: boolean;
};

const OrderPaymentStatus: FC<{
    orderIsPaid: boolean;
    orderHasPaymentInProcess: boolean;
}> = ({ orderIsPaid, orderHasPaymentInProcess }) => {
    const { t } = useTranslation();

    if (orderIsPaid) {
        return (
            <div className="flex items-center gap-2">
                <InfoIconInCircle className="size-4 text-backgroundSuccessMore" />
                {t('The order was paid')}
            </div>
        );
    }

    if (orderHasPaymentInProcess) {
        <div className="flex items-center gap-2">
            <InfoIconInCircle className="size-4 text-backgroundWarningMore" />
            {t('The order is awaiting payment verification.')}
        </div>;
    }

    return (
        <div className="flex items-center gap-2">
            <InfoIconInCircle className="size-4 text-backgroundWarningMore" />
            {t('The order has not been paid')}
        </div>
    );
};

export const OrderPaymentStatusBar: FC<OrderPaymentStatusBarProps> = ({
    orderPaymentType,
    orderIsPaid,
    className,
    orderHasPaymentInProcess,
}) => {
    return (
        <>
            {orderPaymentType === PaymentTypeEnum.GoPay && (
                <div
                    className={twMergeCustom(
                        'flex gap-2 rounded-md p-2',
                        orderIsPaid ? 'bg-backgroundSuccess text-textInverted' : 'bg-backgroundWarning',
                        className,
                    )}
                >
                    <OrderPaymentStatus orderHasPaymentInProcess={orderHasPaymentInProcess} orderIsPaid={orderIsPaid} />
                </div>
            )}
        </>
    );
};
