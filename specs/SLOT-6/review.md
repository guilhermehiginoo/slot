# Review · SLOT-6

Veredito: voltar para o /slot-next

## Problemas

- specs/SLOT-6/spec.md:4 · A linha `Aprovado por:` está vazia, e o código foi escrito mesmo assim. · O AGENTS.md proíbe código de produto antes de uma pessoa do time aprovar a spec. Alguém do time precisa ler a spec e assinar antes do PR.
- tests/Feature/Admin/UserManagementTest.php · Nenhum teste cobre um admin rebaixando outro admin. Só existe o teste de promover (`test_admin_can_promote_another_user_to_admin`). · A spec diz que um admin pode rebaixar outro admin. A regra que bloqueia o próprio admin (R5) compara `$actor->is($user)`, e se alguém trocar isso por `isAdmin()` todo rebaixamento passa a falhar sem nenhum teste ficar vermelho. Conferido à mão: hoje funciona, o outro admin vira aluno e perde o acesso a `/admin`.

## Conferido

- Card x spec: os 6 critérios de aceite estão cumpridos e os 3 cenários do card estão na spec. A spec adiciona 2 cenários (próprio admin e busca), e todos têm teste com `#[TestDox('Cenário: ...')]`.
- Os passos T1 a T5 do `tasks.md` estão marcados. Nada fora do escopo entrou no diff (o menu de professor e de aluno tem só o Dashboard, como diz a spec).
- Backend: o controller faz só o necessário, a validação fica no `UpdateUserRoleRequest` (`Rule::enum`), a regra fica na Action `UpdateUserRole` e a autorização no `UserPolicy`. O grupo `/admin` usa `can:viewAny` e o FormRequest chama `can('update')`. Há testes de 403 para aluno e professor no GET e no PATCH, e de redirect para visitante.
- `role` fica fora do `$fillable`, e há testes de que o cadastro e o perfil ignoram `role`. A migration tem índice e default `student`. Uma tabela só, então não precisa de transação.
- Sem N+1: o `index` faz `select` de 4 colunas, `paginate(15)` e não tem relações. O `auth.user` é o mesmo de antes, agora com `role`. O `insights` do Herd não estava disponível (Herd Pro), então a conferência foi pela leitura do código.
- Frontend: os arquivos estão em `pages/admin/users/` e `components/admin/`. As URLs vêm do Wayfinder (`UserController.index/update`), não tem `any` nem `@ts-ignore`, e o código reaproveita `Select`, `Badge`, `InputError` e `Heading`.
- Em 375px no `slot.test`, logado como admin: a página de usuários não tem rolagem lateral, a linha do próprio admin fica desabilitada com "você", e o menu lateral mostra "Administração › Usuários".
- `composer ci:check` verde: formatação, lint, tipos, Pint, PHPStan e 56 testes (53 passaram, 3 pulados).

## Observação (sem ação neste card)

- Se dois admins se rebaixarem ao mesmo tempo, o sistema pode ficar sem nenhum admin. É improvável no uso real, e o `DatabaseSeeder` ou o `tinker` recuperam. Fica registrado caso o time queira uma regra de "último admin".
