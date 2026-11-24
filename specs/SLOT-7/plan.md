# Plano · SLOT-7

## Banco

- Migration `create_courses_table`: `id`, `string('code', 7)->unique()`, `string('name')`, `timestamp('archived_at')->nullable()->index()` e `timestamps()`.
    - O `unique` de `code` garante R4 mesmo com duas requisições ao mesmo tempo e serve ao `orderBy('code')` (R9).
    - O índice de `archived_at` serve aos filtros de arquivadas das SLOT-8, SLOT-11 e SLOT-13, como o do `role` na SLOT-6.
- Model `App\Models\Course` (disciplina): `#[Fillable(['code', 'name'])]`, cast `archived_at => datetime`, `isArchived(): bool` e `@property` no docblock, como no `User`. `archived_at` fica fora do `$fillable`: só muda pela Action `ArchiveCourse`.
- `CourseFactory`: `code` com `Str::upper(fake()->unique()->bothify('???####'))`, `name` com três palavras e o estado `archived()` (`archived_at = now()`).
- `CourseSeeder`, chamado pelo `DatabaseSeeder`: `updateOrCreate` por `code`, para poder rodar duas vezes, com as 14 disciplinas do anexo da spec (R10).
- A SLOT-8 liga turmas a `courses.id`, por isso o catálogo nunca apaga linhas (R7).

## Backend

| Rota                                            | Controller@método                     | FormRequest               | Action          | Autorização                        |
| ----------------------------------------------- | ------------------------------------- | ------------------------- | --------------- | ---------------------------------- |
| `GET /admin/disciplinas`                        | `Admin\CourseController@index`        | —                         | —               | grupo + `Gate::authorize(viewAny)` |
| `POST /admin/disciplinas`                       | `Admin\CourseController@store`        | `Admin\SaveCourseRequest` | `CreateCourse`  | grupo + `authorize()` (create)     |
| `PATCH /admin/disciplinas/{course}`             | `Admin\CourseController@update`       | `Admin\SaveCourseRequest` | `UpdateCourse`  | grupo + `authorize()` (update)     |
| `POST /admin/disciplinas/{course}/arquivamento` | `Admin\CourseArchiveController@store` | —                         | `ArchiveCourse` | `Gate::authorize(archive)`         |

- Rotas no grupo que já existe em `routes/admin.php` (`auth`, `verified` e `can:viewAny,User`, prefixo `admin`, nomes `admin.*`), o que dá R1:

```php
Route::resource('courses', CourseController::class)->only(['index', 'store', 'update']);
Route::resource('courses.archive', CourseArchiveController::class)->only('store');
```

- Arquivar não cabe no resource de `courses`, então vira outro controller (regra do AGENTS.md): `CourseArchiveController@store`.
- `CoursePolicy` (`viewAny`, `create`, `update`, `archive`): só admin (R1). O grupo já barra professor e aluno; a policy é a segunda trava.
- `Admin\SaveCourseRequest`, uma classe para criar e editar, porque R2 a R6 são as mesmas:
    - `authorize()` chama `create` ou `update` da policy, conforme exista `{course}` na rota.
    - `prepareForValidation()` aplica R2: tira os espaços das pontas e põe o código em maiúsculas.
    - `code`: `required`, `regex:/^[A-Z]{3}\d{4}$/` (R3) e `Rule::unique('courses', 'code')->ignore($this->route('course'))` (R4). `name`: `required`, `string`, `max:255` (R5).
    - `messages()` com os textos da spec: "O código deve ter 3 letras seguidas de 4 dígitos" e "Este código já está em uso". Mensagens de campo vazio e de nome longo (ver Riscos).
- Actions em `App\Actions\Admin\`, como a `UpdateUserRole`:
    - `CreateCourse::handle(array $data): Course` e `UpdateCourse::handle(Course $course, array $data): Course`.
    - `ArchiveCourse::handle(Course $course): void`: grava `archived_at = now()` com `forceFill`; se já está arquivada, não faz nada e a data original fica (R7). Uma tabela só, sem transação.
- `CourseController`: cada método autoriza, valida, chama a Action e responde. `store` e `update` redirecionam para `admin.courses.index` com o toast "Disciplina salva com sucesso!" (`Inertia::flash`, como no `UserController`).
- `CourseArchiveController@store` chama a Action e volta com `back()`, que mantém a busca e a página, com o toast "Disciplina arquivada com sucesso!".

## Queries

- `index`: `Course::query()->select(['id', 'code', 'name', 'archived_at'])`, `where` em grupo com `whereLike('code')` ou `whereLike('name')` quando houver `search` (com `trim`, como no `UserController`), `orderBy('code')` e `paginate(15)->withQueryString()` (R9). Sem relações, sem N+1. O `archived_at` entra no `select` por causa do modo estrito.
- Props: `courses` (paginator só com essas colunas) e `filters.search`. Os estados vazios saem das props, sem query extra: `courses.total === 0` com `search` nulo é "Nenhuma disciplina cadastrada", e com `search` é "Nenhuma disciplina encontrada".

## Frontend

- `types/admin.ts`: `AdminCourse` (`id`, `code`, `name`, `archived_at: string | null`).
- `components/pagination.tsx` (compartilhado): prop opcional `label`. Com `label="disciplinas"`, o texto central vira "Exibindo X–Y de Z disciplinas" e a barra aparece mesmo com uma página só, como no protótipo. Sem `label`, a tela de usuários não muda. Pede `from`, `to` e `total` no `Pick` do tipo.
- `components/empty-state.tsx` (raiz, uso geral): título, descrição e ação opcional, usado pelos dois estados vazios. Não existe nada parecido em `components/`, então ele é novo.
- `components/admin/course-actions.tsx`: botão Editar (link do Wayfinder) e Arquivar, que abre o diálogo; em disciplina arquivada, o segundo botão vira "Arquivada" desabilitado. Área de toque de 44px no celular.
- `components/admin/course-list.tsx`: de `md:` em diante, um `<table>` simples (não existe `ui/table`) com Código, Nome, Status e Ações; no celular, cada disciplina vira um card com código e selo (`Badge` "Ativa" ou "Arquivada"), nome e ações.
- `components/admin/archive-course-dialog.tsx`: confirmação com os textos da spec. Usa `<Form>` com `CourseArchiveController.store.form(course.id)`, botões "Arquivar disciplina" e "Cancelar", e fecha no `onSuccess`. Usa o `Dialog` que já existe em `components/ui/`, sem primitivo novo.
- `components/admin/course-form-dialog.tsx`: modal com Código e Nome (`Label`, `Input` e `InputError`), a dica "Identificador oficial da disciplina no IMD." e os botões "Cancelar" (fecha o modal) e "Salvar disciplina". Sem disciplina, envia para `store`; com disciplina, para `update`. Fecha no `onSuccess`.
- Páginas em `pages/admin/courses/`:
    - `index.tsx`: título, "+ Nova disciplina", busca no mesmo formato da tela de usuários, lista, paginação e estados vazios; guarda no estado a disciplina que está sendo arquivada.
- Menu: item "Disciplinas" abaixo de "Usuários" no grupo "Administração" de `hooks/use-nav-groups.ts`, com o ícone `GraduationCap` e `CourseController.index()`.
- Depois de criar as rotas, rodar `php artisan wayfinder:generate --with-form`: os arquivos de `@/actions` não vão para o Git e o `types:check` falha sem eles.
- Celular (375px): sem rolagem horizontal; nos formulários, "Salvar disciplina" e "Cancelar" empilhados em largura total, como no protótipo.

## Testes

Tudo em `tests/Feature/Admin/CourseManagementTest`, com `RefreshDatabase`, factories e `#[TestDox('Cenário: ...')]` em cada teste de cenário.

| Cenário da spec                        | Teste                                                   |
| -------------------------------------- | ------------------------------------------------------- |
| Admin cria uma disciplina              | `test_admin_creates_a_course`                           |
| O código é normalizado                 | `test_the_code_is_normalized`                           |
| Código inválido                        | `test_the_code_must_have_three_letters_and_four_digits` |
| Código repetido                        | `test_the_code_must_be_unique`                          |
| Admin edita uma disciplina             | `test_admin_edits_a_course`                             |
| Salvar sem mudar o código              | `test_saving_without_changing_the_code_is_allowed`      |
| Admin arquiva uma disciplina           | `test_admin_archives_a_course`                          |
| Cancelar o arquivamento                | sem teste de feature (ver Riscos)                       |
| Buscar por código ou nome              | `test_admin_searches_courses_by_code_or_name`           |
| Estados vazios                         | `test_empty_states_come_from_total_and_search`          |
| Quem não é admin não acessa o catálogo | `test_non_admins_cannot_open_the_courses_pages`         |

Testes de apoio: nome obrigatório e com no máximo 255 caracteres; editar uma disciplina arquivada; código de arquivada continua reservado; arquivar duas vezes mantém a data; lista ordenada por código e paginada de 15 em 15; visitante vai para o login; professor e aluno recebem 403 em criar, editar e arquivar; `DatabaseSeederTest` confere as 14 disciplinas e que rodar o seeder duas vezes não duplica.

## Riscos

- O cenário "Cancelar o arquivamento" é só de interface: o diálogo fecha sem enviar nada. Não há o que provar em PHPUnit, então a revisão confere à mão.
- O `Pagination` é compartilhado com a tela de usuários. A prop `label` é opcional para não mudar o comportamento dela, e o protótipo diz "Próximo" onde o componente diz "Próxima" (mantido).
- O `Dialog` e o tipo `Paginated` (`from`, `to`, `total`) já foram conferidos no repositório: o `Paginated` tem esses campos e o `Dialog` existe, então não há primitivo novo.
- O `LIKE` do SQLite só ignora maiúsculas em ASCII: buscar "PROGRAMAÇÃO" não acha "Programação". A busca sem acento é da SLOT-13, e os testes usam texto sem acento.
- As mensagens de campo vazio ("Informe o código da disciplina.", "Informe o nome da disciplina.") e de nome longo não estão na spec nem no protótipo. São suposição para o revisor conferir.
- Arquivar uma disciplina já arquivada responde com sucesso e não altera nada, em vez de erro: o botão já fica desabilitado, e uma requisição repetida não deve quebrar a tela.
- Com o modo estrito, a listagem seleciona `archived_at` explicitamente; qualquer tela nova que leia essa coluna precisa fazer o mesmo.
