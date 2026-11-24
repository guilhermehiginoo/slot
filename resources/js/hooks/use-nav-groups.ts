import { usePage } from '@inertiajs/react';
import { GraduationCap, LayoutGrid, School, Users } from 'lucide-react';
import ClassGroupController from '@/actions/App/Http/Controllers/Admin/ClassGroupController';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import { dashboard } from '@/routes';
import type { NavGroup, Role } from '@/types';

const platformGroup: NavGroup = {
    label: 'Plataforma',
    items: [
        {
            title: 'Visão geral',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ],
};

const groupsByRole: Record<Role, NavGroup[]> = {
    admin: [
        {
            label: 'Acadêmico',
            items: [
                {
                    title: 'Disciplinas',
                    href: CourseController.index(),
                    icon: GraduationCap,
                },
                {
                    title: 'Turmas',
                    href: ClassGroupController.index(),
                    icon: School,
                },
            ],
        },
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
