import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

export function NavMain({ items, label }: { items: NavItem[]; label: string }) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <SidebarGroup className="gap-6 p-0">
            <SidebarGroupLabel className="h-5 px-0 text-[11px] leading-5 font-semibold text-sidebar-foreground uppercase">
                {label}
            </SidebarGroupLabel>
            <SidebarMenu className="gap-1.5">
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton
                            asChild
                            isActive={isCurrentUrl(item.href)}
                            className="h-12 rounded-[10px] px-4 text-sm leading-5 data-[active=true]:font-semibold"
                        >
                            <Link href={item.href} prefetch>
                                {item.title}
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
