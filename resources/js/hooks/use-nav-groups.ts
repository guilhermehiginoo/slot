import { usePage } from '@inertiajs/react';
import {
    CalendarDays,
    GraduationCap,
    LayoutGrid,
    School,
    Users,
} from 'lucide-react';
import ClassGroupController from '@/actions/App/Http/Controllers/Admin/ClassGroupController';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import SlotController from '@/actions/App/Http/Controllers/Monitoring/SlotController';
import ProfessorClassGroupController from '@/actions/App/Http/Controllers/Professor/ClassGroupController';
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
    professor: [
        {
            label: 'Acadêmico',
            items: [
                {
                    title: 'Minhas turmas',
                    href: ProfessorClassGroupController.index(),
                    icon: School,
                },
            ],
        },
    ],
    student: [],
};

export function useNavGroups(): NavGroup[] {
    const { auth, canManageSlots } = usePage().props;

    const groups = [platformGroup, ...groupsByRole[auth.user.role]];

    if (canManageSlots) {
        groups.push({
            label: 'Minhas monitorias',
            items: [
                {
                    title: 'Meus horários',
                    href: SlotController.index(),
                    icon: CalendarDays,
                },
            ],
        });
    }

    return groups;
}
