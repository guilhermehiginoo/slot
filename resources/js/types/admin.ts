import type { Role } from '@/types/auth';

export type AdminUser = {
    id: number;
    name: string;
    email: string;
    role: Role;
};

export type AdminCourse = {
    id: number;
    code: string;
    name: string;
    archived_at: string | null;
};

export type CourseOption = {
    id: number;
    code: string;
    name: string;
};

export type ProfessorOption = {
    id: number;
    name: string;
};

export type AdminClassGroup = {
    id: number;
    semester: string;
    number: string;
    schedule: string;
    course: CourseOption;
    professors: ProfessorOption[];
};
