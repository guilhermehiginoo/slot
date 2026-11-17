import { Form, Head, usePage } from '@inertiajs/react';
import { Search } from 'lucide-react';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import { UserRoleForm } from '@/components/admin/user-role-form';
import Heading from '@/components/heading';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
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

            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Usuários"
                    description="Busque uma pessoa e mude o papel dela."
                />

                <Form
                    {...UserController.index.form()}
                    options={{ preserveState: true, replace: true }}
                    className="flex gap-2"
                >
                    <Label htmlFor="search" className="sr-only">
                        Buscar por nome ou e-mail
                    </Label>
                    <Input
                        id="search"
                        name="search"
                        type="search"
                        defaultValue={filters.search ?? ''}
                        placeholder="Buscar por nome ou e-mail"
                    />
                    <Button type="submit" variant="secondary">
                        <Search />
                        <span className="sr-only md:not-sr-only">Buscar</span>
                    </Button>
                </Form>

                {users.data.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Nenhum usuário encontrado.
                    </p>
                ) : (
                    <ul className="divide-y rounded-xl border px-4">
                        {users.data.map((user) => (
                            <UserRoleForm
                                key={user.id}
                                user={user}
                                isCurrentUser={user.id === auth.user.id}
                            />
                        ))}
                    </ul>
                )}

                <Pagination page={users} />
            </div>
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Usuários',
            href: UserController.index(),
        },
    ],
};
