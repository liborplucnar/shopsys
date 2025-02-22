import { SortingBarProps } from './SortingBar';
import { FilterIcon } from 'components/Basic/Icon/FilterIcon';
import { SkeletonModuleFilterAndSortingBar } from 'components/Blocks/Skeleton/SkeletonModuleFilterAndSortingBar';
import { Button } from 'components/Forms/Button/Button';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { useDeferredRender } from 'utils/useDeferredRender';

const SortingBar = dynamic(() => import('./SortingBar').then((component) => component.SortingBar), {
    ssr: false,
    loading: () => <SkeletonModuleFilterAndSortingBar />,
});

export const DeferredFilterAndSortingBar: FC<SortingBarProps> = ({ ...sortingBarProps }) => {
    const { t } = useTranslation();
    const shouldRender = useDeferredRender('sorting_bar');
    const setIsFilterPanelOpen = useSessionStore((s) => s.setIsFilterPanelOpen);

    return shouldRender ? (
        <div className="relative flex flex-col items-center justify-between gap-2.5 sm:flex-row vl:border-b vl:border-borderAccentLess">
            <Button
                className="w-full flex-1 justify-start sm:w-auto vl:hidden"
                variant="secondary"
                onClick={() => setIsFilterPanelOpen(true)}
            >
                <FilterIcon className="size-5" />
                {t('Filter')}
            </Button>
            <SortingBar {...sortingBarProps} />
        </div>
    ) : (
        <SkeletonModuleFilterAndSortingBar />
    );
};
