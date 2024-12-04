import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageRegistrationApp: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <div className="mx-auto flex w-full max-w-3xl flex-col">
            <Skeleton className="h-10 w-72" containerClassName="flex mb-5" />
            <Skeleton className="h-[1000px]" containerClassName="flex w-full" />
        </div>
    </Webline>
);
