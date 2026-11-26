import { usePage } from '@inertiajs/react';
import ClassGroupController from '@/actions/App/Http/Controllers/Admin/ClassGroupController';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import SlotController from '@/actions/App/Http/Controllers/Monitoring/SlotController';
import ProfessorClassGroupController from '@/actions/App/Http/Controllers/Professor/ClassGroupController';
import { dashboard } from '@/routes';
import type { NavItem, NavSpace } from '@/types';

const overview: NavItem = { title: 'Visão geral', href: dashboard() };

const mySlots: NavItem = {
    title: 'Meus horários',
    href: SlotController.index(),
};

const spaces = {
    admin: {
        label: 'Espaço administrativo',
        profile: 'Administração',
        items: [
            overview,
            { title: 'Usuários', href: UserController.index() },
            { title: 'Disciplinas', href: CourseController.index() },
            { title: 'Turmas', href: ClassGroupController.index() },
        ],
    },
    professor: {
        label: 'Espaço do professor',
        profile: 'Professor',
        items: [
            {
                title: 'Minhas turmas',
                href: ProfessorClassGroupController.index(),
            },
        ],
    },
    student: {
        label: 'Espaço do aluno',
        profile: 'Aluno',
        items: [overview],
    },
    monitor: {
        label: 'Espaço do monitor',
        profile: 'Monitor',
        items: [overview, mySlots],
    },
} satisfies Record<string, NavSpace>;

export function useNavSpace(): NavSpace {
    const { auth, canManageSlots } = usePage().props;
    const role = auth.user.role;

    if (role === 'student') {
        return canManageSlots ? spaces.monitor : spaces.student;
    }

    if (canManageSlots) {
        return { ...spaces[role], items: [...spaces[role].items, mySlots] };
    }

    return spaces[role];
}
