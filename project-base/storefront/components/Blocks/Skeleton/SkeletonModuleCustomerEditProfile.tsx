import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomerEditProfile: FC = () => (
    <SkeletonModuleCustomer>
        <div className="w-full max-w-3xl">
            <Skeleton className="mb-4 h-11 w-72" />

            <Skeleton className="h-[1000px] w-full" />
        </div>
    </SkeletonModuleCustomer>
);
