import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { SkeletonManager } from 'components/Blocks/Skeleton/SkeletonManager';
import { Footer } from 'components/Layout/Footer/Footer';
import { Header } from 'components/Layout/Header/Header';
import { NotificationBars } from 'components/Layout/NotificationBars/NotificationBars';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';
import { useSessionStore } from 'store/useSessionStore';
import { useOrderPagesAccess } from 'utils/cart/useOrderPagesAccess';

type OrderLayoutProps = {
    page: 'transport-and-payment' | 'contact-information';
    isFetchingData?: boolean;
};

export const OrderLayout: FC<OrderLayoutProps> = ({ children, page, isFetchingData }) => {
    const { t } = useTranslation();
    const canContentBeDisplayed = useOrderPagesAccess(page);
    const isPageLoading = useSessionStore((s) => s.isPageLoading);

    return (
        <div className="h-full">
            <SeoMeta defaultTitle={t('Order')} />

            <NotificationBars />

            <Webline
                className="relative mb-8"
                wrapperClassName="bg-gradient-to-tr from-backgroundBrand to-backgroundBrandLess"
            >
                <Header simpleHeader />
            </Webline>

            <Adverts withGapBottom withWebline positionName="header" />

            <div className="h-full min-h-[70vh]">
                <SkeletonManager
                    isFetchingData={!canContentBeDisplayed || isFetchingData}
                    isPageLoading={isPageLoading}
                    pageTypeOverride={page}
                >
                    <Webline>{children}</Webline>
                </SkeletonManager>
            </div>

            <Adverts withGapBottom withWebline positionName="footer" />

            <Webline wrapperClassName="bg-backgroundAccentLess">
                <Footer simpleFooter />
            </Webline>
        </div>
    );
};
