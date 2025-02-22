import { StyleguideSection } from './StyleguideElements';
import { useEffect, useState } from 'react';

type StyleguideIconsProps = { iconList: string[] };

type IconType = {
    Icon: FC | undefined;
    name: string;
};

const IconComponent: FC<IconType> = ({ Icon, name }) =>
    Icon ? (
        <>
            <Icon className="w-10" />
            <span>{name}</span>
        </>
    ) : (
        <span className="max-w-40 text-sm">
            Check that icon&apos;s name matches the filename:{' '}
            <span className="text-base font-medium">{`/components/Basic/Icon/${name}`}</span>
        </span>
    );

export const StyleguideIcons: FC<StyleguideIconsProps> = ({ iconList }) => {
    const [icons, setIcons] = useState<IconType[]>();

    const getAllIcons = () =>
        iconList.map(async (iconFileNameWithExtension) => {
            const iconFileName = iconFileNameWithExtension.split('.')[0];
            const Icon = (await import(`/components/Basic/Icon/${iconFileName}`))[iconFileName];

            return { Icon, name: iconFileName };
        });

    useEffect(() => {
        const fetch = async () => {
            const resolvedIcons = await Promise.all(getAllIcons());

            setIcons(resolvedIcons);
        };

        fetch();
    }, []);

    return (
        <StyleguideSection className="gap-3 md:columns-2 lg:columns-3" title="Icons">
            {icons?.map(({ Icon, name }, index) => (
                <div key={index} className="mt-3 flex items-center gap-3 first:mt-0">
                    <IconComponent Icon={Icon} name={name} />
                </div>
            ))}
        </StyleguideSection>
    );
};
