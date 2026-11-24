import { usePage } from '@inertiajs/react';
import { GraduationCap, LayoutGrid, Users } from 'lucide-react';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
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
                {
                    title: 'Disciplinas',
                    href: CourseController.index(),
                    icon: GraduationCap,
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
