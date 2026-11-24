import { Form, Head, usePage } from '@inertiajs/react';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import { UserRoleForm } from '@/components/admin/user-role-form';
import { EmptyState } from '@/components/empty-state';
import { Pagination } from '@/components/pagination';
import { SearchIcon } from '@/components/search-icon';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminUser, Paginated } from '@/types';

type Props = {
    users: Paginated<AdminUser>;
    filters: {
        search: string | null;
    };
};

export default function UsersIndex({ users, filters }: Props) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Usuários" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:px-11">
                <header className="space-y-1">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Usuários
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Busque uma pessoa e mude o papel dela.
                    </p>
                </header>

                <Form
                    {...UserController.index.form()}
                    options={{ preserveState: true, replace: true }}
                    className="relative md:max-w-xl"
                >
                    <SearchIcon className="pointer-events-none absolute top-1/2 left-3.5 h-[15px] w-3.5 -translate-y-1/2 text-primary" />
                    <Label htmlFor="search" className="sr-only">
                        Buscar por nome ou e-mail
                    </Label>
                    <Input
                        id="search"
                        name="search"
                        type="search"
                        defaultValue={filters.search ?? ''}
                        placeholder="Buscar por nome ou e-mail"
                        className="h-11 bg-card pl-10"
                    />
                </Form>

                {users.data.length === 0 ? (
                    <EmptyState
                        title="Nenhum usuário encontrado"
                        description="Tente buscar por outro nome ou e-mail."
                    />
                ) : (
                    <div className="overflow-hidden rounded-xl border bg-card">
                        <ul className="divide-y px-4">
                            {users.data.map((user) => (
                                <UserRoleForm
                                    key={user.id}
                                    user={user}
                                    isCurrentUser={user.id === auth.user.id}
                                />
                            ))}
                        </ul>
                        <div className="border-t p-4 md:py-3">
                            <Pagination page={users} label="usuários" />
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [
        { title: 'Administração', href: UserController.index() },
        { title: 'Usuários', href: UserController.index() },
    ],
};
