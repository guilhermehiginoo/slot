# AGENTS.md

Constituição do Slot. Vale para pessoas e para agentes de IA. Toda regra aqui é obrigatória; quando algo não estiver coberto, siga o padrão do código que já existe e pergunte antes de inventar um novo.

## Projeto

Slot é um sistema de agendamento de monitorias do IMD/UFRN: monitores publicam horários e alunos agendam atendimentos. Projeto da disciplina de Gestão de Projetos e Metodologias Ágeis, desenvolvido com Scrum em 4 sprints de uma semana.

Stack: Laravel 13, PHP 8.5, React 19 com Inertia, TypeScript, Tailwind e Wayfinder. Testes em PHPUnit, banco SQLite.

Board: [Jira](https://slotgestao.atlassian.net/jira/software/projects/SLOT/boards/1). Backlog completo em [docs/backlog.md](docs/backlog.md).

## Idioma

- Português: specs, planos, tasks, docs, mensagens de commit, descrição de PR e todo texto que o usuário vê na tela.
- Inglês: código. Nomes de classes, métodos, variáveis, nomes de rota, tabelas, colunas e arquivos.
- Português: o caminho das URLs que o usuário vê (`/admin/disciplinas`, `/configuracoes/perfil`). Só o caminho: o nome da rota, o controller e o parâmetro continuam em inglês (`admin.courses.index`, `{course}`). As rotas do Fortify (`/login`, `/register`) ficam como o pacote define.
- Termos do domínio no código: disciplina = `Course`, turma = `ClassGroup`, horário = `Slot`, agendamento = `Booking`, reserva de sala = `RoomReservation`, atendimento = `Session`.

## Comentários

Código não leva comentário explicativo. Nomes claros de classes, métodos e variáveis dizem o que o código faz, os testes com `#[TestDox]` dizem o comportamento, e o porquê fica na spec e no plano do card.

- Ficam só as anotações de tipo que o PHPStan e o TypeScript precisam: `@property`, `@param`, `@return array<...>`, `@var`, `@throws`, `@extends`, `@use`.
- Saem os docblocks que repetem o nome do método (`Run the migrations.`), os `//` que explicam uma linha e os comentários com número de card.
- Se o código só fica claro com um comentário, renomeie ou extraia um método. Se o motivo é uma regra de negócio, ela vai na spec.

## Fluxo SDD

Toda mudança de código nasce de um card do Jira e passa por spec, plano e tasks antes de virar código. Os comandos ficam em `.claude/skills/` e os artefatos de cada card em `specs/SLOT-N/`, commitados junto com o código.

| Ordem | Comando                | O que faz                                                                 | Gera             |
| ----- | ---------------------- | ------------------------------------------------------------------------- | ---------------- |
| 1     | `/slot-specify SLOT-N` | Lê o card, cria a branch, tira as dúvidas e escreve a spec                | `spec.md`        |
| 2     | `/slot-plan`           | Decide como construir, depois que a spec foi aprovada                     | `plan.md`        |
| 3     | `/slot-tasks`          | Quebra o plano em passos do tamanho de um commit                          | `tasks.md`       |
| 4     | `/slot-next`           | Implementa o próximo passo com TDD e commita                              | código           |
| —     | `/slot-commit`         | Commita no padrão do projeto (o `/slot-next` já chama)                    | commit           |
| 5     | `/slot-review`         | Confere card, spec e código, devolve problemas ao `tasks.md` ou abre o PR | `review.md` e PR |

Regras do fluxo:

- Nenhum código de produto antes de a spec ter a linha `Aprovado por:` preenchida por uma pessoa do time.
- A spec é contrato. Se o código não consegue cumprir a spec, pare e atualize a spec; não improvise.
- Use uma sessão nova do Claude para cada comando. O `/slot-review` sempre em sessão nova, para quem revisa não ser quem escreveu.
- Escopo é o card. Trabalho que pertence a outro card fica fora, mesmo que seja pequeno.
- Card do tipo Tarefa não tem código e não passa pelo fluxo.

## TDD

Todo código de produto é escrito em ciclos de teste primeiro:

1. **Vermelho:** escreva o teste do comportamento e rode. Ele precisa falhar, e pelo motivo certo (a funcionalidade não existe, não um erro de digitação no teste).
2. **Verde:** escreva o mínimo de código para o teste passar.
3. **Refatorar:** limpe o código com os testes verdes.

- Cada cenário Gherkin do card vira pelo menos um teste de feature com `#[TestDox('Cenário: <nome do cenário>')]`, para o review achar o teste de cada cenário.
- Bugfix começa por um teste que reproduz o bug e falha.
- Testes de feature em `tests/Feature/<Dominio>/`, no estilo de classe do PHPUnit que o projeto já usa, com `RefreshDatabase` e factories. Nunca dependa de dados criados por outro teste.
- Um teste que passou de primeira, antes do código existir, não prova nada: revise o teste.

## Branches

| Branch    | Papel                                              |
| --------- | -------------------------------------------------- |
| `main`    | Produção. Tudo que está na main está publicado.    |
| `develop` | Integração. O deploy é o merge da develop na main. |

Nunca faça commit direto na `main` ou na `develop`. Todo trabalho acontece em uma branch própria e entra por pull request.

O prefixo da branch vem do tipo do card no Jira:

| Tipo no Jira | Prefixo        | Sai de    | PR para   |
| ------------ | -------------- | --------- | --------- |
| História     | `feature/`     | `develop` | `develop` |
| Enhancement  | `enhancement/` | `develop` | `develop` |
| Bugfix       | `bugfix/`      | `develop` | `develop` |
| Hotfix       | `hotfix/`      | `main`    | `main`    |
| Chore        | `chore/`       | `develop` | `develop` |
| Tarefa       | sem branch     | —         | —         |

Formato: `prefixo/SLOT-N` ou `prefixo/SLOT-N-descricao-em-kebab-case`. A chave do card vai sempre em maiúsculo, porque é ela que liga a branch ao card no Jira. A descrição é opcional, em português, só com letras minúsculas, números e hífens.

Válidos: `feature/SLOT-12-reservar-horario`, `chore/SLOT-5`, `hotfix/SLOT-31-login-quebrado`.
Inválidos: `feature/slot-12`, `feature/12-reservar-horario`, `fix/SLOT-12`, `feature/SLOT-12_reservar`.

A única exceção são as branches `dependabot/*`, que abrem PR para a `develop`.

Depois que um hotfix for mergeado na main, abra um PR da `main` para a `develop`, para a correção não se perder no próximo deploy.

## Commits

Formato: `SLOT-N: (tipo) frase em português`, com a chave igual à da branch.

```
SLOT-12: (test) cobre agendamento em horário lotado
SLOT-12: (feat) permite agendar um horário
SLOT-5: (docs) descreve o fluxo SDD no AGENTS.md
```

Tipos: `feat`, `fix`, `test`, `refactor`, `perf`, `style`, `docs`, `chore`. O hook só valida a chave; o tipo e a frase são padrão do time.

- Um commit por passo do `tasks.md`. Commits pequenos e que deixam os testes verdes.
- Commits feitos por IA não levam coautoria: nada de `Co-Authored-By` de IA nem linha "Generated with". O hook `commit-msg` bloqueia.
- A IA, quando for ela quem commita, roda `composer ci:check` (o mesmo que o CI) antes de cada commit e só commita com tudo verde.
- Nunca use `--no-verify`.

## Pull requests

Título: `SLOT-N: <título do card>`. A descrição segue o [template](.github/pull_request_template.md):

```markdown
## O que mudou

https://slotgestao.atlassian.net/browse/SLOT-N

- <o que mudou, em poucos itens>

## Por que

<o problema que o card resolve e para quem>

## Como testar

1. <passo a passo para testar em http://slot.test>
2. <os testes automatizados que cobrem a mudança>

## Prints ou vídeo

<só se a mudança aparecer na tela; sem tela, a seção sai>
```

- O link do card no Jira vai sempre na primeira linha do **O que mudou**.
- A IA só abre PR quando o usuário pede, e com esse formato. Como o `gh` não sobe imagem, quando a mudança tiver tela a IA deixa a seção **Prints ou vídeo** e pede para o usuário anexar o print ou o vídeo.

## Backend

### Controller fino

O controller só liga as peças. Cada método faz, nesta ordem: autoriza, valida, chama a Action, responde.

- **Validação:** sempre em um FormRequest (`app/Http/Requests/<Dominio>/`). Nada de `$request->validate()` no controller.
- **Regra de negócio:** em uma Action (`app/Actions/<Dominio>/<Verbo><Coisa>.php`), uma classe por operação com um método público `handle()`. Escrita em mais de uma tabela vai dentro de `DB::transaction()`.
- **Autorização:** em uma Policy por model. Toda rota que lê ou altera dado de alguém passa por `authorize` ou `can`.
- **Leitura:** query simples pode ficar no controller. Filtro reutilizado vira scope no model.
- Controllers resourceful (`index`, `show`, `create`, `store`, `edit`, `update`, `destroy`). Ação que não cabe nisso vira outro controller, não um método a mais.

### Models e banco

- `$fillable` explícito, `casts()` para datas, booleanos e enums, relações com tipo de retorno.
- Status e papéis em PHP enums (`app/Enums/`), nunca strings soltas.
- Migrations com `foreignId()->constrained()`, índice em toda coluna usada em `where`, `orderBy` ou `join`, e `unique` onde a regra de negócio exige unicidade (por exemplo, um aluno não pode ter dois agendamentos no mesmo horário).
- Todo model novo tem factory e um seeder com alguns registros realistas, chamado pelo `DatabaseSeeder`, para o app abrir populado em todas as visões. O seeder é idempotente (rodar de novo não duplica nada) e tem teste em `tests/Feature/Database/DatabaseSeederTest`.

### Performance e N+1

- O app roda com `Model::shouldBeStrict()` fora de produção: acessar relação sem eager loading lança exceção nos testes e no `slot.test`. Corrija com `with()`, nunca desligando o modo estrito.
- Use `with()` para relações e `withCount()` ou `withExists()` para contagens.
- Nada de query dentro de loop, de `map` ou de accessor usado em listagem.
- Toda listagem é paginada.
- Props do Inertia levam só os campos que a página usa: `select()` das colunas ou um array montado, nunca o model inteiro com todas as relações.
- Para conferir uma página, use o `insights` do Herd, que aponta N+1, queries duplicadas e lentas.

## Frontend

Organização em `resources/js/`:

| Pasta                   | O que vai nela                                                                                        |
| ----------------------- | ----------------------------------------------------------------------------------------------------- |
| `pages/<dominio>/`      | Uma página por rota, espelhando o controller: `CourseController@index` vira `pages/courses/index.tsx` |
| `components/<dominio>/` | Componentes de um domínio: `components/courses/course-card.tsx`                                       |
| `components/` (raiz)    | Componentes de layout e de uso geral, como os que já existem                                          |
| `components/ui/`        | Só primitivos do shadcn. Não coloque regra de negócio aqui                                            |
| `hooks/`                | Hooks reutilizáveis, sempre `use-<nome>.ts`                                                           |
| `types/<dominio>.ts`    | Tipos das props que vêm do backend                                                                    |
| `lib/`                  | Funções puras, sem React                                                                              |

- Arquivos em kebab-case e em inglês. Um componente por arquivo.
- Página só monta a tela: busca nada, recebe tudo por props do Inertia. Pedaço de tela com lógica própria ou com mais de umas 150 linhas vira componente.
- URLs só pelo Wayfinder (`@/actions/...` e `@/routes/...`). Nunca escreva uma URL como string.
- Formulários com o `<Form>` ou `useForm` do Inertia e erros com o `InputError`.
- Antes de criar um componente, procure em `components/` e `components/ui/`. Reaproveitar vem primeiro.
- Mobile first: classes sem prefixo valem para o celular, `md:` e `lg:` para telas maiores. Toda página precisa funcionar com 375px de largura.
- TypeScript estrito: nada de `any` e nada de `@ts-ignore`.

## Validação automática

Dois hooks do Husky, ativados com `npm run prepare` depois de clonar (o `.npmrc` usa `ignore-scripts=true`):

- **`commit-msg`:** a mensagem começa com a chave da branch (`SLOT-N: `) e não tem coautoria de IA.
- **`pre-push`:** nome da branch, formatação e lint do frontend e do Markdown (`vp check`), Laravel Pint (`--test`), PHPStan com Larastan e os testes. Se a formatação reclamar, rode `npx vp check --fix` e `./vendor/bin/pint` e commite o resultado.

No GitHub, o workflow `branch-name` valida nome e destino de cada PR (a `main` só recebe da `develop` ou de `hotfix/*`, e a `develop` não recebe `hotfix/*`), e o workflow `tests` roda `composer ci:check`.

Se o nome da branch estiver errado, renomeie com `git branch -m <novo-nome>`.

## Definição de pronto

- Todos os critérios de aceite do card estão cumpridos e cada cenário tem seu teste.
- `composer ci:check` verde e o CI verde no PR.
- Nenhum N+1, nenhuma regra de negócio no controller, toda rota com autorização.
- Telas novas funcionam com 375px de largura.
- O PR foi revisado por outra pessoa do time e o card está atualizado no Jira.
