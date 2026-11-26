import { Link, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Button } from '@/components/ui/button';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    useSidebar,
} from '@/components/ui/sidebar';
import { useNavSpace } from '@/hooks/use-nav-space';
import { dashboard } from '@/routes';

export function AppSidebar() {
    const { label, profile, items } = useNavSpace();
    const { currentSemester } = usePage().props;
    const { isMobile, setOpenMobile } = useSidebar();

    return (
        <Sidebar collapsible="offcanvas" className="border-sidebar-border">
            <SidebarHeader className="flex-row items-start justify-between gap-3 px-3.5 pt-6 pb-0">
                <Link
                    href={dashboard()}
                    prefetch
                    className="flex items-center gap-3"
                >
                    <AppLogo />
                </Link>
                {isMobile && (
                    <Button
                        variant="outline"
                        className="h-11 rounded-[10px] bg-sidebar px-4 font-semibold"
                        onClick={() => setOpenMobile(false)}
                    >
                        Fechar
                    </Button>
                )}
            </SidebarHeader>

            <SidebarContent className="px-3.5 pt-6">
                <NavMain label={label} items={items} />
            </SidebarContent>

            <SidebarFooter className="gap-4 px-3.5 pb-6">
                <NavUser />
                <div className="flex flex-col gap-1 leading-5 text-sidebar-foreground">
                    <p className="text-[11px] font-semibold uppercase">
                        Slot · {profile}
                    </p>
                    <p className="text-xs leading-5">
                        IMD / UFRN · Semestre {currentSemester}
                    </p>
                </div>
            </SidebarFooter>
        </Sidebar>
    );
}
