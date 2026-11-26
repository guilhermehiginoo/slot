# Plano · SLOT-9

## Banco

- Migration `create_class_monitor_table` (pivô da SLOT-10): `foreignId('class_group_id')->constrained()->cascadeOnDelete()`, `foreignId('user_id')->constrained()`, `foreignId('appointed_by')->constrained('users')`, `timestamp('appointed_at')`.
    - `primary(['class_group_id', 'user_id'])`: um aluno é monitor de uma turma uma vez só, e serve à contagem da R3 e à lista da R6.
    - Índice em `user_id`: a SLOT-10 e a SLOT-11 procuram as turmas de um monitor.
- Migration `create_monitor_invitations_table`: `id`, `class_group_id` (cascata), `user_id` (aluno), `invited_by` (`users`), `string('status', 10)->default('pending')`, `timestamps()`. Índices `['class_group_id', 'status']` (R7) e `user_id` (convites do aluno, SLOT-10). O `unique` de um convite pendente por aluno e turma fica para a SLOT-10, que cria os convites.
- Migration `create_monitor_applications_table`: `id`, `class_group_id` (cascata), `user_id` (aluno), `string('message', 500)->nullable()`, `string('status', 10)->default('pending')`, `timestamps()`. Índices `['class_group_id', 'status']` (R8) e `user_id`.
- Enums `MonitorInvitationStatus` (`pending`, `accepted`, `declined`, `revoked`) e `MonitorApplicationStatus` (`pending`, `approved`, `rejected`), como no backlog.
- Pivot `ClassMonitor` (`Pivot`, cast `appointed_at` para `datetime`).
- `ClassGroup`: `monitors(): BelongsToMany` (`User`, tabela `class_monitor`, `using(ClassMonitor)`, `withPivot(['appointed_by', 'appointed_at'])`), `monitorInvitations(): HasMany`, `monitorApplications(): HasMany`, scope `taughtBy(User)` (`whereRelation` em `professors`).
- Models `MonitorInvitation` e `MonitorApplication`: fillable, cast do status, `classGroup()` e `student()` (`BelongsTo User` por `user_id`), scope `pending()`. O convite também tem `invitedBy()`.
- Factories `MonitorInvitationFactory` e `MonitorApplicationFactory` (status pendente, estados para os outros status).
- `MonitorSeeder`, chamado depois do `ClassGroupSeeder` (R10): quatro alunos com `updateOrCreate` por e-mail; João e Marina com `syncWithoutDetaching` em IMD0030 T01 (`appointed_by` Ana); convite do Lucas e solicitação da Beatriz com `firstOrCreate` por turma e aluno.

## Backend

| Rota                                 | Controller@método                      | Autorização                         |
| ------------------------------------ | -------------------------------------- | ----------------------------------- |
| `GET /professor/turmas`              | `Professor\ClassGroupController@index` | `Gate::authorize('viewTaught')` R1  |
| `GET /professor/turmas/{classGroup}` | `Professor\ClassGroupController@show`  | `Gate::authorize('view', turma)` R4 |

- `routes/professor.php`, incluído no `web.php`: grupo `auth`, `verified`, prefixo `professor`, nomes `professor.`, `Route::resource('turmas', ...)->only(['index', 'show'])->names('class-groups')->parameters(['turmas' => 'classGroup'])`.
- `ClassGroupPolicy`: `viewTaught(User)` = papel professor; `view(User, ClassGroup)` = papel professor e `professors()->whereKey($user->id)->exists()`.
- Sem FormRequest nem Action: as duas rotas só leem (R9).

## Queries

- `index`: `ClassGroup::currentSemester()->taughtBy($user)`, `select(['id', 'course_id', 'number', 'schedule'])`, `with('course:id,code,name')`, `withCount('monitors')`, ordem pela subquery do código da disciplina e por `number` (como no admin), `paginate(15)`, `through()` com `id`, `number`, `schedule`, `course {code, name}`, `monitors_count`. Props também levam `currentSemester`. Três queries.
- `show`: `load(['course:id,code,name', 'professors' ordenados por nome com users.id e users.name])`; monitores por `ClassMonitor` (pivô tipado, com `monitor:id,name` e ordem pela subquery do nome, porque o `$user->pivot` não é tipado para o PHPStan); convites `monitorInvitations()->pending()->with('student:id,name')->latest()`; solicitações `monitorApplications()->pending()->with('student:id,name')->oldest()`. Arrays só com os campos da tela; datas em ISO 8601.

## Frontend

- `types/professor.ts`: `TaughtClassGroup`, `ClassGroupDetails`, `ClassMonitor`, `PendingInvitation`, `PendingApplication`.
- `pages/professor/class-groups/index.tsx`: cabeçalho, lista, `Pagination label="turmas"` e `EmptyState`.
- `pages/professor/class-groups/show.tsx`: cabeçalho com selos e as três seções.
- `components/professor/taught-class-group-list.tsx`: tabela de `xl:` em diante e cards antes, como a `ClassGroupList` do admin; "Gerenciar turma" é um `Link` para `ClassGroupController.show`.
- `components/professor/people-section.tsx`: título, colunas e linhas; tabela no desktop, cards no celular, mensagem de vazio. Usado nas três seções.
- `lib/dates.ts`: `formatDate` com `Intl.DateTimeFormat('pt-BR')`.
- Menu: grupo "Acadêmico" com "Minhas turmas" para `professor` em `use-nav-groups.ts`, ícone `School`.
- Depois das rotas: `php artisan wayfinder:generate --with-form`.
- 375px: cards empilhados, botões com 44px, sem rolagem horizontal.

## Testes

`tests/Feature/Professor/ProfessorClassGroupsTest`:

| Cenário da spec                           | Teste                                                        |
| ----------------------------------------- | ------------------------------------------------------------ |
| O professor vê só as próprias turmas      | `test_professors_only_see_their_own_class_groups`            |
| Professor abre a turma de outro professor | `test_professors_cannot_open_class_groups_they_do_not_teach` |

Apoio: só o semestre atual; contagem de monitores por turma; ordem e 15 por página; admin, aluno e visitante em "Minhas turmas"; página da turma com monitores, convites e solicitações, sem os não pendentes; admin e professor que virou aluno recebem 403 na turma; turma de semestre anterior abre. `DatabaseSeederTest`: dados de monitoria do seeder e rodar duas vezes sem duplicar; atualizar a lista de usuários do seeder.

## Riscos

- `test_seeder_creates_the_users_of_each_role` lista os e-mails exatos e quebra com os alunos novos: o passo do seeder atualiza o teste.
- Colunas faltando no `select` dos monitores, convites e solicitações: o modo estrito acusa nos testes.
- A SLOT-10 pode estar sendo planejada em paralelo: avisar o time de que as tabelas nascem aqui.
