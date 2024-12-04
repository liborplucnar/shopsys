import { InfoIconInCircle } from 'components/Basic/Icon/InfoIconInCircle';
import { TypePaymentTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'utils/twMerge';

type OrderPaymentStatusBarProps = {
    orderPaymentType: TypePaymentTypeEnum;
    orderIsPaid: boolean;
};

export const OrderPaymentStatusBar: FC<OrderPaymentStatusBarProps> = ({ orderPaymentType, orderIsPaid, className }) => {
    const { t } = useTranslation();
    return (
        <>
            {orderPaymentType === TypePaymentTypeEnum.GoPay && (
                <div
                    className={twMergeCustom(
                        'flex gap-2 rounded-md p-2',
                        orderIsPaid ? 'bg-backgroundSuccess text-textInverted' : 'bg-backgroundWarning',
                        className,
                    )}
                >
                    {orderIsPaid ? (
                        <>
                            <InfoIconInCircle className="w-4 text-backgroundSuccessMore" />
                            {t('The order was paid')}
                        </>
                    ) : (
                        <>
                            <InfoIconInCircle className="w-4 text-backgroundWarningMore" />
                            {t('The order has not been paid')}
                        </>
                    )}
                </div>
            )}
        </>
    );
};
