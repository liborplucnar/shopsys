import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type ProductPriceProps = {
    productPrice: TypeProductPriceFragment;
    isPriceFromVisible?: boolean;
    placeholder?: string;
};

export const ProductPrice: FC<ProductPriceProps> = ({ productPrice, isPriceFromVisible, placeholder, className }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    if (!isPriceVisible(productPrice.priceWithVat)) {
        return placeholder ?? null;
    }

    return (
        <>
            {!!productPrice.percentageDiscount && (
                <div className="text-xl font-bold text-textError">
                    {productPrice.isPriceFrom && isPriceFromVisible && t('From') + '\u00A0'}
                    {formatPrice(productPrice.priceWithVat)}
                </div>
            )}
            <div
                className={twMergeCustom(
                    'z-above font-secondary text-base font-bold text-price sm:text-lg',
                    className,
                    !!productPrice.percentageDiscount && 'line-through',
                )}
            >
                {productPrice.isPriceFrom && isPriceFromVisible && t('From') + '\u00A0'}
                {formatPrice(productPrice.basicPrice.priceWithVat)}
            </div>
        </>
    );
};
