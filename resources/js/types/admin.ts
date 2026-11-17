import type { Role } from '@/types/auth';

export type AdminUser = {
    id: number;
    name: string;
    email: string;
    role: Role;
};
