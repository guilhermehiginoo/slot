# Tasks · SLOT-13

- [x] T1 · Busca de disciplinas por código ou nome sem acento, com a página e o item no menu
    - Teste: `tests/Feature/Courses/SearchCoursesTest::test_finds_by_code_prefix` (Cenário: Busca pelo código), `::test_finds_by_name_without_accents` (Cenário: Parte do nome sem acento), `::test_hides_archived` (Cenário: Disciplina arquivada não aparece), `::test_no_results` (Cenário: Nenhum resultado); também busca vazia em ordem de código e acesso de professor, aluno e visitante (R3, R4, R7)
    - Arquivos: migration `add_description_and_search_name_to_courses_table`, `app/Models/Course.php` (mutator `name`, scopes `active` e `search`), `routes/web.php`, `app/Http/Controllers/CourseController.php`, `Admin\ClassGroupController` e `SaveClassGroupRequest` (trocam o `whereNull` pelo `active()`), `resources/js/types/courses.ts`, `pages/courses/index.tsx`, `components/courses/course-result-list.tsx`, `hooks/use-nav-space.ts`
- [x] T2 · Página da disciplina com as turmas do semestre atual e 404 para arquivada
    - Teste: `tests/Feature/Courses/ShowCourseTest::test_shows_current_class_groups` (Cenário: Abrir uma disciplina), `::test_archived_is_not_found` (Cenário: Página de disciplina arquivada); também turmas de outro semestre fora e nenhuma prop com `email` (R9, R13)
    - Arquivos: `Course::classGroups()`, `CoursePolicy::view`, `CourseController@show`, `pages/courses/show.tsx`, `components/courses/class-group-card.tsx`
- [x] T3 · Descrição e professores anteriores na página da disciplina
    - Teste: `ShowCourseTest::test_shows_description` (Cenário: Descrição da disciplina), `::test_lists_past_professors` (Cenário: Professores que já deram a disciplina); também semestre futuro fora (R10)
    - Arquivos: `User::taughtClassGroups()`, `CourseFactory::withDescription()`, `CourseController@show`, `pages/courses/show.tsx`
- [x] T4 · Admin escreve a descrição no modal do catálogo
    - Teste: `tests/Feature/Admin/CourseManagementTest::test_saves_description` (Cenário: Admin escreve a descrição), `::test_rejects_long_description` (Cenário: Descrição longa demais)
    - Arquivos: `Admin\SaveCourseRequest`, `CreateCourse`, `UpdateCourse`, `Admin\CourseController`, `components/ui/textarea.tsx`, `components/admin/course-form-dialog.tsx`, `types/admin.ts`
- [ ] T5 · Conferir busca, página da disciplina e modal com 375px e no desktop
    - Teste: `composer ci:check` verde e conferência no navegador
    - Arquivos: só ajustes de layout, se precisar
