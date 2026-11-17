# Plano · SLOT-6

## Banco

- Enum `App\Enums\Role` (string): `Admin = 'admin'`, `Professor = 'professor'`, `Student = 'student'` (R1).
- Migration `add_role_to_users_table`: `string('role')->default('student')->index()` depois de `email`. O índice serve às listagens por papel dos próximos cards (professores, monitores).
- Model `User`:
    - cast `role => Role::class` e `$attributes = ['role' => 'student']`, para o model recém-criado já ter o papel sem reler do banco (o modo estrito lança exceção em atributo ausente).
    - `role` fica fora do `$fillable`: só muda pela Action do admin, nunca por mass assignment do cadastro ou do perfil (R2, R4).
    - `isAdmin(): bool`.
- Migration `add_admin_since_to_users_table`: `timestamp('admin_since')->nullable()` depois de `role`. Os admins que já existem recebem `admin_since = created_at` (R8).
- Model `User`: cast `admin_since => datetime`, fora do `$fillable` como o `role`.
- `UserFactory`: padrão `student`, estados `admin()` (com `admin_since = now()`) e `professor()`.
- `DatabaseSeeder`: um usuário de cada papel com a factory (`admin@slot.test`, `professor@slot.test`, `student@slot.test`).

## Backend

| Rota                        | Controller@método             | FormRequest                   | Action           | Autorização                    |
| --------------------------- | ----------------------------- | ----------------------------- | ---------------- | ------------------------------ |
| `GET /admin/users`          | `Admin\UserController@index`  | —                             | —                | grupo `can:viewAny,User`       |
| `PATCH /admin/users/{user}` | `Admin\UserController@update` | `Admin\UpdateUserRoleRequest` | `UpdateUserRole` | grupo + `authorize()` (update) |

- Rotas em `routes/admin.php`, incluído pelo `web.php`, no grupo `auth`, `verified` e `can:viewAny,App\Models\User`, prefixo `admin` e nomes `admin.*` (R3). O middleware `can` devolve 403 para professor e aluno.
- `UserPolicy`: `viewAny` e `update` só para admin (R3, R4).
- `UpdateUserRoleRequest`: `authorize()` chama a policy `update`; `role` obrigatório e `Rule::enum(Role::class)`.
- Action `App\Actions\Admin\UpdateUserRole::handle(User $actor, User $user, Role $role)`: se `$actor` é o `$user` e o novo papel não é admin, lança `ValidationException` no campo `role` com "Você não pode tirar o seu próprio papel de admin." (R5). Se o `$user` é admin, deixa de ser admin e não virou admin depois do `$actor` (`admin_since` mais novo), lança `ValidationException` no `role` com "Você não pode rebaixar um admin mais antigo que você." (R8). Ao promover grava `admin_since = now()`, ao rebaixar grava `null`. Uma tabela só, sem transação.
- O controller responde com redirect de volta e um toast "Papel atualizado." pelo `Inertia::flash`, como o `ProfileController`.
- R6 sai de graça: o papel é lido do banco a cada requisição, junto com o usuário logado.

## Queries

- `index`: `User::query()->select(['id', 'name', 'email', 'role'])`, `where` em grupo com `name like` ou `email like` quando houver `search`, `orderBy('name')`, `paginate(15)->withQueryString()`. Sem relações, sem N+1.
- Props: `users` (paginator só com essas colunas) e `filters.search`.
- Prop compartilhada: `auth.user` já vai inteiro para o front; com a coluna nova, `auth.user.role` passa a existir (critério da prop compartilhada).

## Frontend

- `types/auth.ts`: `Role = 'admin' | 'professor' | 'student'` e `role: Role` no `User`.
- `types/pagination.ts`: tipo `Paginated<T>` do paginator do Laravel.
- `types/admin.ts`: `AdminUser` (`id`, `name`, `email`, `role`).
- `lib/roles.ts`: rótulos (Admin, Professor, Aluno) e a lista de papéis para o seletor.
- `pages/admin/users/index.tsx`: título, busca (`<Form>` GET do Wayfinder com o campo `search`), lista e paginação.
- `components/admin/user-role-form.tsx`: linha com nome, e-mail, `Select` do papel e botão Salvar (`useForm` + `InputError`). Desabilitado na linha do próprio admin, com a marca "você".
- `components/pagination.tsx` (raiz, uso geral): Anterior, "Página X de Y" e Próxima, com as URLs do paginator.
- Menu: `hooks/use-nav-groups.ts` monta os grupos do menu pelo papel de `auth.user.role` (todos: Dashboard; admin: grupo "Administração" com Usuários). `NavMain` ganha a prop `label`; `app-sidebar` e `app-header` usam o hook (R7).
- `UserInfo` mostra o rótulo do papel quando mostra o e-mail (menu do usuário).
- Celular (375px): cada usuário vira um bloco empilhado (nome e e-mail em cima, seletor e botão embaixo); de `md:` em diante, tudo em uma linha.

## Testes

| Cenário da spec                                 | Teste                                                                                      |
| ----------------------------------------------- | ------------------------------------------------------------------------------------------ |
| Novo cadastro entra como aluno                  | `tests/Feature/Auth/RegistrationTest::test_new_users_register_as_students`                 |
| Quem não é admin não acessa as páginas de admin | `tests/Feature/Admin/UserManagementTest::test_non_admins_cannot_open_the_admin_users_page` |
| Admin muda um papel                             | `tests/Feature/Admin/UserManagementTest::test_admin_changes_a_role`                        |
| Admin não tira o próprio papel de admin         | `tests/Feature/Admin/UserManagementTest::test_admin_cannot_remove_their_own_admin_role`    |
| Admin busca um usuário                          | `tests/Feature/Admin/UserManagementTest::test_admin_searches_users_by_name_or_email`       |

Testes de apoio: prop `auth.user.role` (`tests/Feature/Auth/RoleSharedPropTest`), papel inválido, professor sem acesso, visitante vai para o login, e o seeder (`tests/Feature/Database/DatabaseSeederTest`).

## Riscos

- Modo estrito e atributo ausente no usuário recém-criado: resolvido com o `$attributes` padrão no model.
- `role` no `$fillable` abriria troca de papel pelo perfil: fica fora, e a Action grava direto.
