# Tasks · SLOT-8

- [x] T1 · O admin lista as turmas filtradas por disciplina e semestre; os demais recebem 403; scope do semestre atual
    - Teste: `tests/Feature/Admin/ClassGroupManagementTest` (Cenários: Filtrar por disciplina e semestre; Quem não é admin não acessa as turmas), mais ordem e 15 por página, opções de semestre, `hasClassGroups`, visitante vai ao login e `currentSemester()` trocando o config (R12)
    - Arquivos: `config/slot.php`, `.env.example`, migrations `create_class_groups_table` e `create_class_group_user_table`, `app/Models/ClassGroup.php`, `database/factories/ClassGroupFactory.php`, `app/Policies/ClassGroupPolicy.php`, `routes/admin.php`, `app/Http/Controllers/Admin/ClassGroupController.php`
- [x] T2 · O admin cria uma turma com número normalizado, semestre validado, só professores e só disciplina ativa
    - Teste: `tests/Feature/Admin/ClassGroupManagementTest` (Cenários: Admin cria uma turma; Turma repetida; Só professores dão aula; O número é normalizado; Semestre inválido; Disciplina arquivada não recebe turma nova), mais obrigatórios e "Selecione ao menos um professor", horário acima de 50, número "T1" e "A01", mesma turma em outro semestre e 403 em criar
    - Arquivos: `app/Http/Requests/Admin/SaveClassGroupRequest.php`, `app/Actions/Admin/CreateClassGroup.php`, `app/Http/Controllers/Admin/ClassGroupController.php`
- [x] T3 · O admin edita uma turma, inclusive de disciplina arquivada, trocando os professores
    - Teste: `tests/Feature/Admin/ClassGroupManagementTest` (Cenário: Admin edita uma turma), mais editar sem mudar o número, editar turma de disciplina arquivada, remover professor e 403 em editar
    - Arquivos: `app/Http/Requests/Admin/SaveClassGroupRequest.php`, `app/Actions/Admin/UpdateClassGroup.php`, `app/Http/Controllers/Admin/ClassGroupController.php`
- [x] T4 · O seeder cria 2 professores e 4 turmas em 2026.2, sem duplicar ao rodar de novo
    - Teste: `tests/Feature/Database/DatabaseSeederTest` (turmas do seeder e rodar duas vezes); atualizar `test_seeder_creates_one_user_of_each_role`, que quebra com os professores novos
    - Arquivos: `database/seeders/ClassGroupSeeder.php`, `database/seeders/DatabaseSeeder.php`
- [x] T5 · Tela da lista: tabela no desktop e cards no celular, filtros, paginação "Exibindo X–Y de Z turmas", estados vazios e item "Turmas" no menu
    - Teste: `php artisan wayfinder:generate --with-form`, `npm run types:check` e conferência no navegador no desktop
    - Arquivos: `resources/js/pages/admin/class-groups/index.tsx`, `resources/js/components/admin/class-group-filters.tsx`, `resources/js/components/admin/class-group-list.tsx`, `resources/js/types/admin.ts`, `resources/js/hooks/use-nav-groups.ts`
- [ ] T6 · Modais de criar e editar turma com o seletor de professores, e conferência das telas em 375px
    - Teste: `npm run types:check` e conferência no navegador em 375px e no desktop (criar, editar, buscar professor, erros ao lado do campo, toast "Turma salva com sucesso!")
    - Arquivos: `resources/js/components/admin/class-group-form-dialog.tsx`, `resources/js/components/admin/professor-picker-dialog.tsx`, `resources/js/pages/admin/class-groups/index.tsx`
