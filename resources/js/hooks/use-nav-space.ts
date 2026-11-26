import { usePage } from '@inertiajs/react';
import ClassGroupController from '@/actions/App/Http/Controllers/Admin/ClassGroupController';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import SlotController from '@/actions/App/Http/Controllers/Monitoring/SlotController';
import ProfessorClassGroupController from '@/actions/App/Http/Controllers/Professor/ClassGroupController';
import { dashboard } from '@/routes';
import type { NavGroup, NavItem, NavSpace } from '@/types';

const overview: NavItem = { title: 'Visão geral', href: dashboard() };

const mySlots: NavItem = {
    title: 'Meus horários',
    href: SlotController.index(),
};

const monitorGroup: NavGroup = {
    label: 'Espaço do monitor',
    items: [mySlots],
};

const spaces = {
    admin: {
        groups: [
            {
                label: 'Espaço administrativo',
                items: [
                    overview,
                    { title: 'Usuários', href: UserController.index() },
                    { title: 'Disciplinas', href: CourseController.index() },
                    { title: 'Turmas', href: ClassGroupController.index() },
                ],
            },
        ],
    },
    professor: {
        groups: [
            {
                label: 'Espaço do professor',
                items: [
                    {
                        title: 'Minhas turmas',
                        href: ProfessorClassGroupController.index(),
                    },
                ],
            },
        ],
    },
    student: {
        groups: [{ label: 'Espaço do aluno', items: [overview] }],
    },
} satisfies Record<string, NavSpace>;

export function useNavSpace(): NavSpace {
    const { auth, canManageSlots } = usePage().props;
    const space = spaces[auth.user.role];

    if (!canManageSlots) {
        return space;
    }

    return {
        groups: [...space.groups, monitorGroup],
    };
}
