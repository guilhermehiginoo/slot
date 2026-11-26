import { usePage } from '@inertiajs/react';
import ClassGroupController from '@/actions/App/Http/Controllers/Admin/ClassGroupController';
import AdminCourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import CourseController from '@/actions/App/Http/Controllers/CourseController';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import SlotController from '@/actions/App/Http/Controllers/Monitoring/SlotController';
import ProfessorClassGroupController from '@/actions/App/Http/Controllers/Professor/ClassGroupController';
import { dashboard } from '@/routes';
import type { NavGroup, NavItem, NavSpace } from '@/types';

const overview: NavItem = { title: 'Visão geral', href: dashboard() };

const searchCourses: NavItem = {
    title: 'Buscar disciplinas',
    href: CourseController.index(),
};

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
        profile: 'Administração',
        groups: [
            {
                label: 'Espaço administrativo',
                items: [
                    overview,
                    searchCourses,
                    { title: 'Usuários', href: UserController.index() },
                    {
                        title: 'Disciplinas',
                        href: AdminCourseController.index(),
                    },
                    { title: 'Turmas', href: ClassGroupController.index() },
                ],
            },
        ],
    },
    professor: {
        profile: 'Professor',
        groups: [
            {
                label: 'Espaço do professor',
                items: [
                    searchCourses,
                    {
                        title: 'Minhas turmas',
                        href: ProfessorClassGroupController.index(),
                    },
                ],
            },
        ],
    },
    student: {
        profile: 'Aluno',
        groups: [
            { label: 'Espaço do aluno', items: [overview, searchCourses] },
        ],
    },
} satisfies Record<string, NavSpace>;

export function useNavSpace(): NavSpace {
    const { auth, canManageSlots } = usePage().props;
    const space = spaces[auth.user.role];

    if (!canManageSlots) {
        return space;
    }

    return {
        profile: auth.user.role === 'student' ? 'Monitor' : space.profile,
        groups: [...space.groups, monitorGroup],
    };
}
