import { Menu } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { Button } from '@/components/ui/button';
import { SidebarTrigger, useSidebar } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { toggleSidebar } = useSidebar();

    return (
        <>
            <header className="flex h-[78px] shrink-0 items-center gap-3 border-b border-sidebar-border bg-sidebar px-5 md:hidden">
                <Button
                    variant="ghost"
                    size="icon"
                    className="size-11 [&_svg]:size-5!"
                    onClick={toggleSidebar}
                    aria-label="Abrir menu"
                >
                    <Menu />
                </Button>
                <AppLogoIcon className="size-9 shrink-0" />
                <span className="text-[25px] leading-[34px] font-bold text-[#071a4b] dark:text-sidebar-accent-foreground">
                    Slot
                </span>
            </header>
            <header className="flex h-12 shrink-0 items-center gap-2 px-4 md:h-16 md:border-b md:border-sidebar-border/50">
                <SidebarTrigger className="-ml-1 hidden md:inline-flex" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </header>
        </>
    );
}
