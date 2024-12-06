import { Button } from 'components/Forms/Button/Button';
import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';

const WatchdogPopup = dynamic(
    () => import('components/Blocks/Popup/WatchdogPopup').then((component) => component.WatchdogPopup),
    {
        ssr: false,
    },
);

type ProductAvailabilityProps = {
    availability: TypeAvailability;
    availableStoresCount: number | null;
    isInquiryType: boolean;
    productUuid?: string;
    productIsSellingDenied?: boolean;
    onClick?: () => void;
};

export const ProductAvailability: FC<ProductAvailabilityProps> = ({
    availability,
    availableStoresCount,
    className,
    isInquiryType,
    productUuid,
    productIsSellingDenied,
    onClick,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const openWatchDogPopup = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
        e.stopPropagation();

        if (productUuid) {
            updatePortalContent(<WatchdogPopup productUuid={productUuid} />);
        }
    };

    const showWatchdogButton =
        (productUuid && !isInquiryType && availability.status === TypeAvailabilityStatusEnum.OutOfStock) ||
        productIsSellingDenied;

    return (
        <div className="flex flex-col gap-1">
            <div
                className={twJoin(
                    className,
                    'text-sm',
                    availability.status === TypeAvailabilityStatusEnum.InStock && 'text-availabilityInStock',
                    availability.status === TypeAvailabilityStatusEnum.OutOfStock && 'text-availabilityOutOfStock',
                )}
                onClick={onClick}
            >
                {!isInquiryType &&
                    `${availability.name}${
                        availability.status !== TypeAvailabilityStatusEnum.OutOfStock && availableStoresCount !== null
                            ? `, ${t('ready to ship immediately')} ${availableStoresCount !== 0 ? t('or at {{ count }} stores', { count: availableStoresCount }) : ''}`
                            : ''
                    }`}
            </div>

            {showWatchdogButton && (
                <Button className="mr-auto" variant="inverted" onClick={openWatchDogPopup}>
                    {t('Watch the goods')}
                </Button>
            )}
        </div>
    );
};
