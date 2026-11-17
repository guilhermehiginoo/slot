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

export function useNavGroups(): NavGroup[] {
    const { auth } = usePage().props;

    return [platformGroup, ...groupsByRole[auth.user.role]];
}
