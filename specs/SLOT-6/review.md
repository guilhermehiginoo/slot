# Review · SLOT-6

Veredito: aprovado

## Problemas

Nenhum.

## Conferido

- Os dois problemas do review anterior foram resolvidos: a spec tem `Aprovado por: Guilherme Higino` (T6) e existe o teste `test_older_admin_demotes_a_newer_admin` (T7), que rebaixa outro admin e confere o 403 depois.
- Card x spec: todos os critérios de aceite estão cumpridos, inclusive a coluna `admin_since` da R8. Os 7 cenários da spec têm teste com `#[TestDox('Cenário: ...')]` em `UserManagementTest` e `RegistrationTest`.
- `tasks.md`: T1 a T8 marcados.
- R8 na Action `UpdateUserRole`: só rebaixa outro admin quem tem `admin_since` estritamente mais antigo. Um `admin_since` nulo ou igual recusa a mudança, que é o lado seguro. O admin mais antigo nunca é rebaixado, então sempre sobra um admin, mesmo com dois agindo ao mesmo tempo (a observação do review anterior fica resolvida). Promover grava `admin_since`, rebaixar limpa, e a migration preenche os admins que já existem com o `created_at`.
- Backend: o controller autoriza (grupo `can:viewAny` + `authorize()` do FormRequest), valida (`Rule::enum`), chama a Action e responde. `role` e `admin_since` ficam fora do `$fillable`, e há testes de que o cadastro e o perfil ignoram `role`. Há testes de 403 para aluno e professor e de redirect para visitante. Uma tabela só, sem transação.
- Sem N+1: o `index` faz `select` de 4 colunas, `paginate(15)` e não carrega relações. O `insights` do Herd não está disponível (Herd Pro), então isso foi conferido lendo o código.
- Frontend: arquivos em `pages/admin/users/` e `components/admin/`, URLs pelo Wayfinder, sem `any` nem `@ts-ignore`, e o código reaproveita `Select`, `Badge`, `InputError` e `Heading`. O erro da R8 aparece pelo `InputError` embaixo do seletor.
- Em 375px no `slot.test`, logado como admin, `/admin/users` não tem rolagem lateral (`scrollWidth` 375) e a linha do próprio admin fica desabilitada com "você".
- `composer ci:check` verde: formatação, lint, tipos, Pint, PHPStan e 58 testes (55 passaram, 3 pulados).

## Observações (sem ação neste card)

- `AGENTS.md` ganhou a seção "Comentários", que é regra do projeto inteiro e não do card. O commit de refactor só mexe em arquivos do card, então o risco é baixo, mas vale citar no PR para o time saber da regra nova.
- A tabela de Testes do `plan.md` não lista os dois cenários da R8. O `tasks.md` (T7 e T8) aponta os testes certos.
