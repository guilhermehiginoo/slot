# Slot

Sistema de agendamento de monitorias. Monitores publicam seus horários de atendimento, alunos reservam um horário e todo mundo sabe quando e onde vai ser a monitoria, sem depender de grupo de WhatsApp ou planilha.

Projeto da disciplina de Gestão de Projetos e Metodologias Ágeis (UFRN/IMD), desenvolvido com Scrum em 4 sprints de uma semana.

**Board:** [GitHub Projects](https://github.com/users/guilhermehiginoo/projects/7)

## Funcionalidades planejadas

| Sprint | Objetivo |
| --- | --- |
| 1 | Monitor publica horários e aluno reserva |
| 2 | Cancelamento, regras de antecedência e fila de espera |
| 3 | Notificações e avaliação do atendimento |
| 4 | Painel do professor, ajustes finais e apresentação |

## Stack

| Camada | Tecnologia |
| --- | --- |
| Backend | Laravel 13, PHP 8.5 |
| Frontend | React 19, Inertia, TypeScript, Tailwind |
| Banco local | SQLite |
| Qualidade | Pest/PHPUnit, Laravel Pint, PHPStan com Larastan, Husky |

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

| Comando | O que faz |
| --- | --- |
| `composer dev` | Sobe o Vite e os processos de desenvolvimento |
| `php artisan test` | Roda os testes |
| `./vendor/bin/pint` | Formata o código PHP |
| `./vendor/bin/phpstan analyse` | Roda a análise estática |
| `npm run types:check` | Verifica os tipos do TypeScript |
| `composer ci:check` | Roda tudo que o CI roda |

## Fluxo de trabalho

A `main` é produção e a `develop` é a branch de integração. Ninguém commita direto nelas: todo trabalho acontece em uma branch própria e entra por pull request.

| Prefixo | Quando usar | Sai de | PR para |
| --- | --- | --- | --- |
| `feature/` | Funcionalidade nova | `develop` | `develop` |
| `enhancement/` | Melhoria em algo que já existe | `develop` | `develop` |
| `bugfix/` | Bug que pode esperar o próximo deploy | `develop` | `develop` |
| `hotfix/` | Bug em produção que precisa ser corrigido agora | `main` | `main` |
| `chore/` | Manutenção, configuração, dependências, docs | `develop` | `develop` |

O nome segue o formato `prefixo/descricao-em-kebab-case`, de preferência com o número da issue: `feature/12-reservar-horario`. Depois de um hotfix, a `main` volta pra `develop` por PR.

### Validações automáticas

Antes de cada push, o hook do Husky valida o nome da branch, roda o Pint em modo de verificação e o PHPStan. No GitHub, cada pull request passa pela validação de nome e destino da branch e pela suíte de testes.

Não use `--no-verify` pra pular o hook. Se o Pint reclamar, rode `./vendor/bin/pint` e commite a formatação.

## Time

| Pessoa | Papel |
| --- | --- |
| Guilherme Higino | Product Owner e dev |
| Adrian Felipe | Scrum Master e dev |
| Renan Avelino | Dev |
| Maurício Costa | Dev |
| Marcos Antonio | QA |
