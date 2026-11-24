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
