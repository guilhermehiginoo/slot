# Tasks · SLOT-9

- [x] T1 · "Minhas turmas" lista as turmas do professor no semestre atual com a quantidade de monitores; quem não é professor recebe 403
    - Teste: `tests/Feature/Professor/ProfessorClassGroupsTest` (Cenário: O professor vê só as próprias turmas), mais semestre atual, contagem de monitores, ordem e 15 por página, admin, aluno e visitante
    - Arquivos: migration `create_class_monitor_table`, `app/Models/ClassMonitor.php`, `app/Models/ClassGroup.php`, `app/Policies/ClassGroupPolicy.php`, `routes/professor.php`, `routes/web.php`, `app/Http/Controllers/Professor/ClassGroupController.php`
- [x] T2 · A página da turma lista monitores, convites pendentes e solicitações pendentes, e só abre para quem dá aula nela
    - Teste: `tests/Feature/Professor/ProfessorClassGroupsTest` (Cenário: Professor abre a turma de outro professor), mais as três listas sem os não pendentes, admin, professor que virou aluno e turma de semestre anterior
    - Arquivos: migrations `create_monitor_invitations_table` e `create_monitor_applications_table`, `app/Enums/MonitorInvitationStatus.php`, `app/Enums/MonitorApplicationStatus.php`, `app/Models/MonitorInvitation.php`, `app/Models/MonitorApplication.php`, factories, `app/Policies/ClassGroupPolicy.php`, `app/Http/Controllers/Professor/ClassGroupController.php`
- [x] T3 · O seeder cria dois monitores, um convite e uma solicitação em IMD0030 T01, sem duplicar
    - Teste: `tests/Feature/Database/DatabaseSeederTest` (dados de monitoria e rodar duas vezes); atualizar `test_seeder_creates_the_users_of_each_role`
    - Arquivos: `database/seeders/MonitorSeeder.php`, `database/seeders/DatabaseSeeder.php`
- [x] T4 · Tela "Minhas turmas" com tabela, cards, paginação, estado vazio e item no menu do professor
    - Teste: `php artisan wayfinder:generate --with-form`, `npm run types:check` e conferência no navegador
    - Arquivos: `resources/js/pages/professor/class-groups/index.tsx`, `resources/js/components/professor/taught-class-group-list.tsx`, `resources/js/types/professor.ts`, `resources/js/hooks/use-nav-groups.ts`
- [x] T5 · Tela da turma com as três seções, e conferência das telas em 375px
    - Teste: `npm run types:check` e conferência no navegador em 375px e no desktop
    - Arquivos: `resources/js/pages/professor/class-groups/show.tsx`, `resources/js/components/professor/people-section.tsx`, `resources/js/lib/dates.ts`
