import type { Role } from '@/types';

export const roles: Role[] = ['admin', 'professor', 'student'];

export const roleLabels: Record<Role, string> = {
    admin: 'Admin',
    professor: 'Professor',
    student: 'Aluno',
};

export function isRole(value: string): value is Role {
    return roles.some((role) => role === value);
}
