import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { isRole, roleLabels, roles } from '@/lib/roles';
import type { AdminUser, Role } from '@/types';

type Props = {
    user: AdminUser;
    isCurrentUser: boolean;
};

export function UserRoleForm({ user, isCurrentUser }: Props) {
    const form = useForm<{ role: Role }>({ role: user.role });
    const selectId = `role-${user.id}`;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.submit(UserController.update(user.id), {
            preserveScroll: true,
            onSuccess: () => form.setDefaults(),
        });
    };

    return (
        <li className="flex flex-col gap-3 py-4 md:flex-row md:items-center md:justify-between">
            <div className="min-w-0">
                <p className="flex items-center gap-2 font-medium">
                    <span className="truncate">{user.name}</span>
                    {isCurrentUser && <Badge variant="secondary">você</Badge>}
                </p>
                <p className="truncate text-sm text-muted-foreground">
                    {user.email}
                </p>
            </div>

            <form onSubmit={submit} className="grid gap-2 md:justify-items-end">
                <div className="flex items-center gap-2">
                    <Label htmlFor={selectId} className="sr-only">
                        Papel de {user.name}
                    </Label>
                    <Select
                        value={form.data.role}
                        onValueChange={(value) => {
                            if (isRole(value)) {
                                form.setData('role', value);
                            }
                        }}
                        disabled={isCurrentUser || form.processing}
                    >
                        <SelectTrigger id={selectId} className="flex-1 md:w-40">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {roles.map((role) => (
                                <SelectItem key={role} value={role}>
                                    {roleLabels[role]}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Button
                        type="submit"
                        disabled={
                            isCurrentUser || !form.isDirty || form.processing
                        }
                    >
                        Salvar
                    </Button>
                </div>
                <InputError message={form.errors.role} />
            </form>
        </li>
    );
}
