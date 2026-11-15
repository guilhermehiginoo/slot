# Slot

Sistema de agendamento de monitorias. Monitores publicam seus horários de atendimento, alunos reservam um horário e todo mundo sabe quando e onde vai ser a monitoria, sem depender de grupo de WhatsApp ou planilha.

Projeto da disciplina de Gestão de Projetos e Metodologias Ágeis (UFRN/IMD), desenvolvido com Scrum em 4 sprints de uma semana.

**Board:** [Jira](https://slotgestao.atlassian.net/jira/software/projects/SLOT/boards/1) · **Backlog:** [docs/backlog.md](docs/backlog.md) · **Regras do projeto:** [AGENTS.md](AGENTS.md)

## Funcionalidades planejadas

| Sprint            | Objetivo                                                |
| ----------------- | ------------------------------------------------------- |
| 1 (10/11 – 17/11) | Base do projeto e papéis de usuário                     |
| 2 (17/11 – 24/11) | O monitor consegue publicar horários para uma turma     |
| 3 (24/11 – 01/12) | O aluno encontra uma disciplina e agenda um atendimento |
| 4 (01/12 – 08/12) | Chat sem WhatsApp, salas do IMD e entrega               |

## Stack

| Camada      | Tecnologia                                         |
| ----------- | -------------------------------------------------- |
| Backend     | Laravel 13, PHP 8.5                                |
| Frontend    | React 19, Inertia, TypeScript, Tailwind            |
| Banco local | SQLite                                             |
| Qualidade   | PHPUnit, Laravel Pint, PHPStan com Larastan, Husky |

O projeto parte do starter kit oficial de React do Laravel, com autenticação por Fortify (login, registro, verificação de email e confirmação de senha).

## Rodando localmente

**Pré requisitos:** [Laravel Herd](https://herd.laravel.com) com PHP 8.5 e Composer, e Node 22 ou superior.

```bash
git clone https://github.com/guilhermehiginoo/slot.git ~/Herd/slot
cd ~/Herd/slot
herd isolate 8.5
touch database/database.sqlite
composer setup
npm run prepare
composer dev
```

O `composer setup` instala as dependências, cria o `.env`, gera a chave, roda as migrations e faz o build do front. O `npm run prepare` ativa os hooks do Husky; ele precisa ser rodado à mão porque o `.npmrc` desativa scripts de instalação.

Com o `composer dev` rodando, o app fica em http://slot.test.

## Comandos úteis

| Comando                        | O que faz                                     |
| ------------------------------ | --------------------------------------------- |
| `composer dev`                 | Sobe o Vite e os processos de desenvolvimento |
| `php artisan test`             | Roda os testes                                |
| `./vendor/bin/pint`            | Formata o código PHP                          |
| `./vendor/bin/phpstan analyse` | Roda a análise estática                       |
| `npm run types:check`          | Verifica os tipos do TypeScript               |
| `npx vp check --fix`           | Formata o frontend e o Markdown               |
| `composer ci:check`            | Roda tudo que o CI roda                       |

## Como desenvolver

As regras completas estão no [AGENTS.md](AGENTS.md), que vale para pessoas e para a IA. Esta seção é o caminho prático.

### Antes do primeiro card

1. Rode o projeto seguindo [Rodando localmente](#rodando-localmente), incluindo o `npm run prepare`. Sem ele os hooks não rodam na sua máquina.
2. Instale o [Claude Code](https://claude.com/claude-code). Os comandos `/slot-*` já vêm no repositório, em `.claude/skills/`.
3. Conecte o Atlassian no Claude (opcional). Com ele, os comandos leem o card e movem a coluna no Jira sozinhos. Sem ele, você cola o texto do card e move a coluna à mão.

### O board do Jira

Cada card anda da esquerda para a direita. Os nomes das colunas estão em inglês e os status em português.

| Coluna           | Status       | Significa                                                     | Quem move                     |
| ---------------- | ------------ | ------------------------------------------------------------- | ----------------------------- |
| To Do            | A fazer      | Está na sprint e ninguém pegou ainda                          | —                             |
| In progress      | Em andamento | Alguém pegou e está na spec, no plano ou no código            | `/slot-specify` ou quem pegou |
| PR Review        | Revisando PR | O PR está aberto, esperando a revisão de outra pessoa do time | `/slot-review`, ao abrir o PR |
| Change Requested | Em andamento | A revisão ou o QA pediu mudanças; o card volta para quem fez  | quem revisou                  |
| QA               | GQ           | O PR foi mergeado na `develop` e o QA testa lá                | quem fez o merge              |
| Done             | Concluído    | O QA validou os critérios de aceite                           | o QA                          |

- Pegar um card é se atribuir a ele no Jira. Uma pessoa trabalha em um card de cada vez.
- Bug no escopo do card volta para Change Requested. Bug fora dele vira um card novo do tipo Bugfix.
- Card do tipo Tarefa não tem código: vai de In progress direto para Done.
- Com o GitHub conectado ao Jira, branches, commits e PRs com a chave `SLOT-N` aparecem no painel Desenvolvimento do card.

### Um card do começo ao fim

O exemplo usa a SLOT-12, uma História. Abra o Claude Code na pasta do projeto (`claude`) e use uma sessão nova para cada comando: assim cada etapa lê só o que precisa, e quem revisa não é quem escreveu.

1. **`/slot-specify SLOT-12`** lê o card, cria a branch `feature/SLOT-12-reservar-horario` a partir da `develop` e faz perguntas em rodadas, sempre com uma resposta recomendada. No fim, escreve `specs/SLOT-12/spec.md` com objetivo, regras, cenários, critérios, decisões e suposições.
2. **Aprovação da spec.** Outra pessoa do time (de preferência o PO) lê a spec, principalmente as Suposições, e preenche `Aprovado por: <nome>`. Sem isso, o próximo comando não começa.
3. **`/slot-plan`** decide como construir: migrations, rotas, Actions, Policies, páginas, queries e qual teste prova cada cenário. Gera `plan.md`.
4. **`/slot-tasks`** quebra o plano em passos do tamanho de um commit, cada um com o seu teste. Gera `tasks.md`. Se passar de uns 8 passos, o card é grande demais e deve ser quebrado no Jira.
5. **`/slot-next`**, uma vez por passo. Ele escreve o teste, vê o teste falhar, escreve o código até passar, roda `composer ci:check`, marca o passo e commita com o `/slot-commit`. Se a spec não fechar com o código, ele para e pede para atualizar a spec. Dê `git push` quando quiser.
6. **`/slot-review`** confere card, spec, testes e código: N+1, controller gordo, autorização, organização do frontend e a tela com 375px. Problemas viram passos novos no `tasks.md`, e você volta ao passo 5. Sem problemas, ele abre o PR para a `develop` e move o card para PR Review.
7. **Revisão humana.** Outra pessoa revisa o PR no GitHub. Se pedir mudanças, o card vai para Change Requested e você volta ao `/slot-next`.
8. **Merge e QA.** Com o PR aprovado e o CI verde, faça o merge na `develop` e mova o card para QA. O QA testa na `develop` e move para Done.

Tudo o que foi decidido fica em `specs/SLOT-12/` e entra no PR junto com o código.

Os comandos ajudam, mas não são obrigatórios. Quem preferir trabalhar sem IA segue a mesma ordem e escreve os mesmos arquivos à mão. As regras de branch, commit, TDD e arquitetura são as mesmas.

### Branches e commits

A `main` é produção e a `develop` é a integração. Ninguém commita direto nelas.

| Tipo no Jira | Branch                             | Sai de    | PR para   |
| ------------ | ---------------------------------- | --------- | --------- |
| História     | `feature/SLOT-12-reservar-horario` | `develop` | `develop` |
| Enhancement  | `enhancement/SLOT-18-filtro`       | `develop` | `develop` |
| Bugfix       | `bugfix/SLOT-23-horario-duplicado` | `develop` | `develop` |
| Hotfix       | `hotfix/SLOT-31-login-quebrado`    | `main`    | `main`    |
| Chore        | `chore/SLOT-5-configurar-sdd`      | `develop` | `develop` |

A descrição depois da chave é opcional. Os commits seguem `SLOT-N: (tipo) frase em português`, por exemplo `SLOT-12: (feat) permite agendar um horário`, sem coautoria de IA. Depois de um hotfix, a `main` volta para a `develop` por PR.

### Validações automáticas

- **A cada commit:** o hook `commit-msg` confere se a mensagem começa com a chave da branch e não tem coautoria de IA.
- **A cada push:** o hook `pre-push` valida o nome da branch e roda o `vp` (formatação e lint do frontend e do Markdown), o Pint, o PHPStan e os testes.
- **A cada PR:** o GitHub valida nome e destino da branch e roda `composer ci:check`.

Não use `--no-verify` para pular os hooks. Se a formatação reclamar, rode `npx vp check --fix` e `./vendor/bin/pint` e commite o resultado.

## Time

| Pessoa           | Papel               |
| ---------------- | ------------------- |
| Guilherme Higino | Product Owner e dev |
| Adrian Felipe    | Scrum Master e dev  |
| Renan Avelino    | Dev                 |
| Maurício Costa   | Dev                 |
| Marcos Antonio   | QA                  |
