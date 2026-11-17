import { usePage } from '@inertiajs/react';
import { LayoutGrid, Users } from 'lucide-react';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import { dashboard } from '@/routes';
import type { NavGroup, Role } from '@/types';

const platformGroup: NavGroup = {
    label: 'Plataforma',
    items: [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ],
};

// Professors and students get their own items as their pages arrive (SLOT-9, SLOT-13 onwards).
const groupsByRole: Record<Role, NavGroup[]> = {
    admin: [
        {
            label: 'Administração',
            items: [
                {
                    title: 'Usuários',
                    href: UserController.index(),
                    icon: Users,
                },
            ],
        },
    ],
    professor: [],
    student: [],
};

/** The menu groups of the logged user, based on their role. */
export function useNavGroups(): NavGroup[] {
    const { auth } = usePage().props;

    return [platformGroup, ...groupsByRole[auth.user.role]];
}
