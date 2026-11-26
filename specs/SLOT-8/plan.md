# Plano · SLOT-8

## Banco

- `config/slot.php`: `'current_semester' => env('SLOT_CURRENT_SEMESTER', '2026.2')`, e `SLOT_CURRENT_SEMESTER=2026.2` no `.env.example` (R5). O código lê só `config('slot.current_semester')`.
- Migration `create_class_groups_table`: `foreignId('course_id')->constrained()`, `string('semester', 6)->index()`, `string('number', 3)`, `string('schedule', 50)`, `timestamps()`.
    - `unique(['course_id', 'semester', 'number'])` garante R7 no banco e serve ao filtro por disciplina.
    - O índice de `semester` serve ao filtro da lista (R11) e ao scope do semestre atual (R12).
- Migration `create_class_group_user_table`: `foreignId('class_group_id')->constrained()->cascadeOnDelete()`, `foreignId('user_id')->constrained()`, `primary(['class_group_id', 'user_id'])` (professor não entra duas vezes) e índice em `user_id` (SLOT-9).
- Model `ClassGroup` (turma): `#[Fillable(['course_id', 'semester', 'number', 'schedule'])]`, `@property` como no `Course`, `course(): BelongsTo`, `professors(): BelongsToMany` (`User`, tabela `class_group_user`) e scope `currentSemester()` com `#[Scope]` filtrando `config('slot.current_semester')` (R12).
- `ClassGroupFactory`: `Course::factory()`, `2026.2`, `T01`, `35M12`. Professores com `attach` no teste.
- `ClassGroupSeeder`, chamado depois do `CourseSeeder` (R13): professores "Ana Costa" e "Carlos Alves" (`updateOrCreate` por e-mail) e quatro turmas em `2026.2` (`updateOrCreate` por disciplina, semestre e número, depois `sync`): IMD0030 T01 com os dois, IMD0030 T02, IMD0012 T01 e IMD0029 T01.

## Backend

| Rota                               | Controller@método                   | FormRequest                   | Action             | Autorização                    |
| ---------------------------------- | ----------------------------------- | ----------------------------- | ------------------ | ------------------------------ |
| `GET /admin/turmas`                | `Admin\ClassGroupController@index`  | —                             | —                  | grupo + `Gate::authorize`      |
| `POST /admin/turmas`               | `Admin\ClassGroupController@store`  | `Admin\SaveClassGroupRequest` | `CreateClassGroup` | grupo + `authorize()` (create) |
| `PATCH /admin/turmas/{classGroup}` | `Admin\ClassGroupController@update` | `Admin\SaveClassGroupRequest` | `UpdateClassGroup` | grupo + `authorize()` (update) |

- No grupo de `routes/admin.php`, que já barra quem não é admin (R1): `Route::resource('turmas', ...)->only(['index', 'store', 'update'])->names('class-groups')->parameters(['turmas' => 'classGroup'])`. Sem `destroy` (R10).
- `ClassGroupPolicy` (`viewAny`, `create`, `update`): só admin, como a `CoursePolicy`.
- `SaveClassGroupRequest`, uma classe para criar e editar (R10), no molde do `SaveCourseRequest`:
    - `authorize()` escolhe `create` ou `update` pela rota; `prepareForValidation()` faz `Str::upper(trim(number))` (R6).
    - `course_id`: `required`, `Rule::exists('courses', 'id')` só com `archived_at` nulo; na edição, a disciplina atual da turma também passa (R3).
    - `semester`: `required`, `regex:/^\d{4}\.[12]$/` (R4).
    - `number`: `bail`, `required`, `regex:/^T\d{2}$/` (R6), `Rule::unique('class_groups', 'number')->where(course_id, semester)->ignore(classGroup)` (R7).
    - `professors`: `required`, `array`, `min:1`; `professors.*`: `integer`, `distinct`, `Rule::exists('users', 'id')->where('role', 'professor')` (R9).
    - `schedule`: `required`, `string`, `max:50`, sem formato (R8).
    - `messages()` com os textos da spec.
- Actions em `App\Actions\Admin\`, em `DB::transaction()` porque gravam em duas tabelas: `CreateClassGroup::handle(array $data)` cria e faz `professors()->sync()`; `UpdateClassGroup::handle(ClassGroup, array $data)` atualiza e faz `sync()`.
- `ClassGroupController`: autoriza, valida, chama a Action e volta para `admin.class-groups.index` com o toast "Turma salva com sucesso!".

## Queries

- Filtros (R11): `course` (id ou nulo) e `semester` (padrão o atual).
- Lista: `select(['id', 'course_id', 'semester', 'number', 'schedule'])`, `with(['course:id,code,name', 'professors' => select users.id, users.name, orderBy name])`, `orderBy` por subquery do código da disciplina (sem `join`), depois `number`, `paginate(15)->withQueryString()` e `through()` com só `id`, `semester`, `number`, `schedule`, `course {id, code, name}`, `professors [{id, name}]`. Três queries, sem N+1.
- Opções: disciplinas ativas (`id`, `code`, `name`, por código); professores (`id`, `name`, por nome; `role` já tem índice); semestres = o atual mais `distinct` dos existentes, decrescente.
- `hasClassGroups` (`exists()`) separa "Nenhuma turma cadastrada" de "Nenhuma turma encontrada".

## Frontend

- `types/admin.ts`: `AdminClassGroup`, `CourseOption`, `ProfessorOption`.
- `pages/admin/class-groups/index.tsx`: mesmo esqueleto da página de disciplinas, com breadcrumb, "Nova turma", filtros, lista, `Pagination label="turmas"` e `EmptyState`.
- `components/admin/class-group-filters.tsx`: dois `Select` que fazem `router.get` com `ClassGroupController.index({ query })`.
- `components/admin/class-group-list.tsx`: tabela de `md:` em diante; no celular, um card por turma com "Editar turma" em largura total (44px). Professores com `Intl.ListFormat('pt-BR')` ("Ana Costa e Carlos Alves").
- `components/admin/class-group-form-dialog.tsx`: no molde do `CourseFormDialog`. Disciplina em `Select` com `name="course_id"`, incluindo a disciplina arquivada da turma em edição (R3); semestre preenchido com o atual; professores por um botão que abre o seletor, com um `<input type="hidden" name="professors[]">` por id.
- `components/admin/professor-picker-dialog.tsx`: textos da spec, busca por nome no cliente, `Checkbox` com "selecionado(a)", "Aplicar seleção" e "Cancelar" (descarta).
- Menu: "Turmas" abaixo de "Disciplinas" em `use-nav-groups.ts`, ícone `School`.
- Depois das rotas: `php artisan wayfinder:generate --with-form`.
- 375px: filtros empilhados, sem rolagem horizontal, botões do modal empilhados.

## Testes

`tests/Feature/Admin/ClassGroupManagementTest`, com `#[TestDox('Cenário: ...')]`:

| Cenário da spec                            | Teste                                                    |
| ------------------------------------------ | -------------------------------------------------------- |
| Admin cria uma turma                       | `test_admin_creates_a_class_group`                       |
| Turma repetida                             | `test_course_semester_and_number_must_be_unique`         |
| Só professores dão aula                    | `test_only_professors_can_teach_a_class_group`           |
| O número é normalizado                     | `test_the_number_is_normalized`                          |
| Semestre inválido                          | `test_the_semester_must_end_in_1_or_2`                   |
| Disciplina arquivada não recebe turma nova | `test_archived_courses_do_not_get_new_class_groups`      |
| Admin edita uma turma                      | `test_admin_edits_a_class_group`                         |
| Filtrar por disciplina e semestre          | `test_admin_filters_class_groups_by_course_and_semester` |
| Quem não é admin não acessa as turmas      | `test_non_admins_cannot_open_the_class_groups_pages`     |

Apoio: obrigatórios e "Selecione ao menos um professor"; horário acima de 50; número "T1" e "A01"; editar sem mudar o número; editar turma de disciplina arquivada; remover professor; mesma turma em outro semestre; ordem e 15 por página; opções de semestre; `hasClassGroups`; 403 em criar e editar; visitante vai ao login; scope `currentSemester()` trocando o config (R12). `DatabaseSeederTest`: turmas do seeder e rodar duas vezes sem duplicar (R13).

## Riscos

- O seeder quebra `test_seeder_creates_one_user_of_each_role` (espera três usuários); o passo do seeder atualiza esse teste.
- `exists` com `whereNull` mais a exceção da edição pode virar um `or` solto; os testes de criar e editar com disciplina arquivada cobrem os dois lados.
- Se o `<Form>` não pegar o `<select>` escondido do `Select` do Radix, usar um `<input type="hidden">` controlado.
- Erros de `professors.*` vêm como `professors.0`; o formulário mostra junto com o de `professors`.
- Mensagens de campo vazio e de horário longo não estão na spec: seguem o padrão do SLOT-7, para o revisor conferir.
- Modo estrito: `course_id` e `users.id` precisam estar nos `select` para o eager loading.
