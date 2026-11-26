# Plano · SLOT-13

## Banco

- Migration `add_description_and_search_name_to_courses_table`: `string('description', 500)->nullable()` (R8) e `string('search_name')->default('')->index()` (R2). O `up()` preenche o `search_name` das disciplinas existentes pelo `DB`, em `chunkById`, com a normalização do model.
- Model `Course`:
    - `description` entra no `#[Fillable]`; `search_name` fica fora, porque só muda junto com o nome.
    - Mutator `name()` com `Attribute::set` que devolve `name` e `search_name = Str::lower(Str::ascii($value))` (R2). Vale para `create`, `update`, factory e seeder.
    - Scope `active()` (`whereNull('archived_at')`), trocando os dois `whereNull('archived_at')` soltos do `Admin\ClassGroupController` e do `SaveClassGroupRequest` (filtro reutilizado vira scope).
    - Scope `search(?string $term)`: com termo, `where(code like 'TERMO%' or search_name like '%termo%')`, os dois normalizados como o `search_name` (R1). Sem termo, não filtra (R3).
    - Relação `classGroups(): HasMany`.
- Model `User`: relação `taughtClassGroups(): BelongsToMany` pela pivot `class_group_user`, para os professores anteriores.
- `CourseFactory`: estado `withDescription()`.

## Backend

| Rota                        | Controller@método        | Autorização               |
| --------------------------- | ------------------------ | ------------------------- |
| `GET /disciplinas`          | `CourseController@index` | `auth`, `verified` (R7)   |
| `GET /disciplinas/{course}` | `CourseController@show`  | `Gate::authorize('view')` |

O admin continua salvando por `Admin\CourseController`, `Admin\SaveCourseRequest`, `CreateCourse` e `UpdateCourse` (SLOT-7).

- Rotas em `routes/web.php`, no grupo `auth` + `verified` que já existe: `Route::resource('disciplinas', CourseController::class)->only(['index', 'show'])->names('courses')->parameters(['disciplinas' => 'course:code'])` (R6). Nomes `courses.*`, sem choque com `admin.courses.*`.
- `App\Http\Controllers\CourseController`, só leitura, sem Action. O catálogo não é dado de ninguém, então o `index` só depende do login (R7).
- `CoursePolicy::view(User, Course)`: `Response::denyAsNotFound()` para disciplina arquivada e `allow()` para o resto. Dá o 404 de R6 para todos, admin incluído, sem `abort` no controller.
- `Admin\SaveCourseRequest`: `description` com `nullable`, `string`, `max:500` e a mensagem "A descrição deve ter no máximo 500 caracteres." (R8). O `ConvertEmptyStringsToNull` do Laravel já transforma o texto vazio em `null`.
- `CreateCourse::handle` e `UpdateCourse::handle` ganham o parâmetro `?string $description`; o `Admin\CourseController` passa `$request->input('description')`.

## Queries

- `index`: `Course::query()->active()->search($search)->select(['code', 'name'])->orderBy('code')->paginate(15)->withQueryString()` (R1, R3, R4). O `search` vem do parâmetro `search`, com `trim`, como no catálogo do admin. Props: `courses` e `filters.search`. Sem relações.
- `show`:
    - `course`: `only(['code', 'name', 'description'])`.
    - `classGroups`: `$course->classGroups()->currentSemester()->select(['id', 'course_id', 'number', 'schedule'])->orderBy('number')` com `with()` de `professors` e `monitors`, os dois com `select(['users.id', 'users.name'])->orderBy('users.name')` (R9). Sem N+1. Resposta montada só com `number`, `schedule` e os nomes, sem e-mail (R13).
    - `pastProfessors`: `User::query()->select(['id', 'name'])->whereHas('taughtClassGroups', fn ($q) => $q->where('course_id', $course->id)->where('semester', '<', config('slot.current_semester')))->orderBy('name')->pluck('name')`. Partir de `users` evita repetidos; semestres futuros ficam fora (R10).
    - Turmas e professores anteriores sem paginação (R11).
- `Admin\CourseController@index`: o `select` ganha `description`, para o modal de edição.

## Frontend

- `types/courses.ts`: `CourseSearchResult` (`code`, `name`), `CourseDetails` (`code`, `name`, `description: string | null`) e `CourseClassGroup` (`id`, `number`, `schedule`, `professors: string[]`, `monitors: string[]`).
- `pages/courses/index.tsx`: título "Buscar disciplinas", formulário GET no mesmo formato do catálogo do admin (`SearchIcon`, `Input` com `name="search"`, `CourseController.index.form()`), lista e `Pagination` com `label="disciplinas"`. Sem resultado com busca: `EmptyState` "Nenhuma disciplina encontrada" com a dica "Busque pelo código, como IMD0030, ou por parte do nome.". Sem resultado e sem busca: "Nenhuma disciplina disponível".
- `components/courses/course-result-list.tsx`: cada item é um `Link` para `CourseController.show(course.code)` com `<CÓDIGO> – <Nome>` (R5), com área de toque de 44px.
- `pages/courses/show.tsx`: título com o código e o nome, descrição com `whitespace-pre-line` só quando existe (R8), seção "Turmas em <semestre>" e seção "Professores anteriores" só quando a lista não está vazia. Turma do semestre sem nada: "Nenhuma turma neste semestre". Breadcrumbs: "Buscar disciplinas" › código.
- `components/courses/class-group-card.tsx`: número, horário, professores e monitores; sem monitores, "Sem monitores". Um card por turma, empilhados no celular e em grade de duas colunas de `md:` em diante.
- `components/ui/textarea.tsx`: primitivo do shadcn, que ainda não existe no projeto.
- `components/admin/course-form-dialog.tsx`: campo "Descrição (opcional)" com o `Textarea`, `maxLength={500}`, `defaultValue` da disciplina e `InputError`. `AdminCourse` em `types/admin.ts` ganha `description: string | null`.
- `hooks/use-nav-space.ts`: item "Buscar disciplinas" com `CourseController.index()` logo depois de "Visão geral" no admin, aluno e monitor, e primeiro no professor (R12). O import do controller do admin vira `AdminCourseController`, porque os dois se chamam `CourseController`.
- Depois das rotas, rodar `php artisan wayfinder:generate --with-form`.
- 375px: tudo em uma coluna, sem rolagem horizontal.

## Testes

Classes em `tests/Feature/Courses/` (`SearchCoursesTest`, `ShowCourseTest`) e `tests/Feature/Admin/CourseManagementTest`.

| Cenário                               | Teste                                                   |
| ------------------------------------- | ------------------------------------------------------- |
| Busca pelo código                     | `SearchCoursesTest::test_finds_by_code_prefix`          |
| Parte do nome sem acento              | `SearchCoursesTest::test_finds_by_name_without_accents` |
| Disciplina arquivada não aparece      | `SearchCoursesTest::test_hides_archived`                |
| Nenhum resultado                      | `SearchCoursesTest::test_no_results`                    |
| Abrir uma disciplina                  | `ShowCourseTest::test_shows_current_class_groups`       |
| Descrição da disciplina               | `ShowCourseTest::test_shows_description`                |
| Professores que já deram a disciplina | `ShowCourseTest::test_lists_past_professors`            |
| Página de disciplina arquivada        | `ShowCourseTest::test_archived_is_not_found`            |
| Admin escreve a descrição             | `CourseManagementTest::test_saves_description`          |
| Descrição longa demais                | `CourseManagementTest::test_rejects_long_description`   |

Fora dos cenários: busca vazia lista todas as ativas, em ordem de código (R3, R4); professor e aluno acessam, visitante vai para o login (R7); turmas de outro semestre não aparecem e semestres futuros não contam como anteriores (R9, R10); nenhuma prop leva `email` (R13); o `search_name` muda ao editar o nome (R2).

## Riscos

- O `LIKE` do SQLite só ignora maiúsculas em ASCII. Por isso a busca compara o termo normalizado com o `search_name`, nunca com o `name`.
- Nome salvo fora do model (query direta) deixa o `search_name` velho; hoje todo nome passa pelo model.
- O modo estrito exige que o `select` das turmas leve `course_id` e `id`, senão o eager loading quebra.
- `/disciplinas/imd0030` (minúsculo) dá 404; os links usam o código salvo, em maiúsculas.
- A branch sai do SLOT-35: rebasear sobre a `develop` depois do merge do PR #12.
