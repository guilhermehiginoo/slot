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
| `composer ci:check`            | Roda tudo que o CI roda                       |

## Fluxo de trabalho

Toda mudança de código nasce de um card do Jira e segue o fluxo SDD (spec driven development), com comandos do Claude Code que ficam no próprio repositório:

```
/slot-specify SLOT-N  →  /slot-plan  →  /slot-tasks  →  /slot-next (um por passo)  →  /slot-review
```

1. **`/slot-specify SLOT-N`** lê o card, cria a branch e escreve a spec em `specs/SLOT-N/spec.md`. Alguém do time aprova a spec antes de seguir.
2. **`/slot-plan`** decide como construir.
3. **`/slot-tasks`** quebra o plano em passos do tamanho de um commit.
4. **`/slot-next`** implementa o próximo passo com TDD, roda os testes e commita.
5. **`/slot-review`**, em uma sessão nova, confere tudo contra o card. Problemas voltam como passos novos; sem problemas, abre o PR.

A `main` é produção e a `develop` é a integração. Ninguém commita direto nelas. A branch leva o prefixo do tipo do card e a chave dele, como `feature/SLOT-12-reservar-horario` ou `chore/SLOT-5`, e os commits seguem `SLOT-N: (tipo) frase`. As regras completas estão no [AGENTS.md](AGENTS.md).

### Validações automáticas

- **A cada commit:** o hook `commit-msg` confere se a mensagem começa com a chave da branch e não tem coautoria de IA.
- **A cada push:** o hook `pre-push` valida o nome da branch e roda Pint, PHPStan e os testes.
- **A cada PR:** o GitHub valida nome e destino da branch e roda `composer ci:check`.

Não use `--no-verify` pra pular os hooks. Se o Pint reclamar, rode `./vendor/bin/pint` e commite a formatação.

## Time

| Pessoa           | Papel               |
| ---------------- | ------------------- |
| Guilherme Higino | Product Owner e dev |
| Adrian Felipe    | Scrum Master e dev  |
| Renan Avelino    | Dev                 |
| Maurício Costa   | Dev                 |
| Marcos Antonio   | QA                  |
