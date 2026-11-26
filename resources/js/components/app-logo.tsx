import { usePage } from '@inertiajs/react';

import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    const { name } = usePage().props;

    return (
        <>
            <AppLogoIcon className="size-[42px] shrink-0" />
            <div className="grid flex-1 text-left">
                <span className="text-[33px] leading-10 font-bold text-[#071a4b] dark:text-sidebar-accent-foreground">
                    {name}
                </span>
                <span className="text-[11px] leading-5 font-medium text-[#61718f] dark:text-sidebar-foreground">
                    IMD / UFRN
                </span>
            </div>
        </>
    );
}
