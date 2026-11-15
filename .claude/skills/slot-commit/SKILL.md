---
name: slot-commit
description: Commita as mudanças do Slot no padrão do projeto, "SLOT-N: (tipo) frase", sem coautoria de IA e depois de rodar os testes. Use sempre que for commitar neste repositório.
---

# /slot-commit

## 1. Confira a branch

Tire a chave `SLOT-N` do nome da branch atual. Se estiver na `main` ou na `develop`, pare: nunca se commita direto nelas. Se a branch não tiver chave, pare e peça para criar a branch certa (`/slot-specify`).

## 2. Rode os testes

```bash
composer test
```

Só commite com tudo verde. Se o Pint reclamar, rode `./vendor/bin/pint` e rode `composer test` de novo. Commit que só mexe em arquivos `.md` pode pular esse passo.

## 3. Escolha o que entra

Veja `git status` e `git diff`. Adicione só os arquivos do passo atual, pelo nome (`git add <arquivos>`), nunca `git add -A` às cegas. Nunca commite `.env`, `database/database.sqlite` ou arquivos gerados que o `.gitignore` já ignora.

## 4. Escreva a mensagem

```
SLOT-N: (tipo) frase em português
```

| Tipo       | Quando                                  |
| ---------- | --------------------------------------- |
| `feat`     | comportamento novo                      |
| `fix`      | correção de bug                         |
| `test`     | só testes                               |
| `refactor` | muda o código sem mudar comportamento   |
| `perf`     | desempenho, como tirar um N+1           |
| `style`    | formatação                              |
| `docs`     | specs, planos, tasks, README, AGENTS.md |
| `chore`    | configuração, dependências, CI          |

A frase começa com verbo no presente, em minúsculo, sem ponto final, e diz o que mudou para quem lê o histórico: `SLOT-12: (feat) permite agendar um horário`.

Sem coautoria: nada de `Co-Authored-By` de IA e nada de "Generated with". O hook bloqueia.

```bash
git commit -m "SLOT-N: (tipo) frase"
```

Nunca use `--no-verify`. Se o hook recusar, corrija a causa.

## Fim

Uma linha com o hash curto e a mensagem. Não faça push: isso fica com o `/slot-review` ou com o usuário.
