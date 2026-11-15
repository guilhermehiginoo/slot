---
name: slot-specify
description: Primeiro passo do SDD do Slot. Lê um card do Jira, cria a branch certa e escreve specs/SLOT-N/spec.md depois de tirar todas as dúvidas com o usuário.
argument-hint: SLOT-N
disable-model-invocation: true
---

# /slot-specify SLOT-N

Você transforma um card do Jira em uma spec que qualquer pessoa do time consegue implementar sem perguntar nada. Você não escreve código e não decide como construir: isso é o `/slot-plan`. A spec diz **o quê** e **por quê**.

Siga o AGENTS.md. Escreva a spec em português.

## 1. Leia o card

- Pegue a chave do argumento (`SLOT-N`, em maiúsculo). Sem argumento, pergunte qual card.
- Leia o card pelo conector do Atlassian (`getJiraIssue`, site `slotgestao.atlassian.net`): título, tipo, descrição, cenários Gherkin, critérios de aceite, prioridade e sprint.
- Sem conector, peça para o usuário colar o texto do card e siga com ele.
- Card do tipo Tarefa não tem código: avise e pare.
- Leia `docs/backlog.md` para entender o domínio e de quais cards este depende. Se um card de que ele depende ainda não foi mergeado na `develop`, avise antes de seguir.

## 2. Crie a branch

| Tipo do card | Prefixo        | Base      |
| ------------ | -------------- | --------- |
| História     | `feature/`     | `develop` |
| Enhancement  | `enhancement/` | `develop` |
| Bugfix       | `bugfix/`      | `develop` |
| Hotfix       | `hotfix/`      | `main`    |
| Chore        | `chore/`       | `develop` |

Nome: `<prefixo>SLOT-N-<descricao-curta-em-kebab-case>`, com a descrição tirada do título do card (até umas 5 palavras, sem acento). Exemplo: `feature/SLOT-12-reservar-horario`.

```bash
git fetch origin
git switch -c <branch> origin/<base>
```

Se a branch já existir, só troque para ela. Se houver mudanças não commitadas, pare e pergunte.

Se o conector estiver disponível, mova o card para **Em andamento**.

## 3. Tire as dúvidas em rodadas

Leia o código que o card vai tocar antes de perguntar: fatos você descobre sozinho, decisões são do usuário. Em cada rodada, faça todas as perguntas que já podem ser respondidas, numeradas, cada uma com a sua recomendação:

```
**P1 · <título>:** <pergunta, com as opções se houver>
Recomendo: <resposta> — <por quê, em uma linha>
```

Pare e espere. Continue até não sobrar nada decidido em silêncio: regras de negócio, quem pode fazer o quê, casos de erro, o que aparece na tela, o que fica fora.

- **Bugfix e Hotfix:** pergunte os passos para reproduzir, o que acontece e o que deveria acontecer.
- Se o card for grande demais para uma semana (mais de 8 pontos de esforço real), proponha quebrar em dois cards e diga como.

## 4. Escreva `specs/SLOT-N/spec.md`

Até 6 KB. Corte prosa, nunca decisão.

```markdown
# SLOT-N · <título do card>

Card: https://slotgestao.atlassian.net/browse/SLOT-N
Tipo: <tipo> · Branch: `<branch>` · Base: `<base>`
Aprovado por:

## Objetivo

<o problema e para quem, em 2 ou 3 frases>

## Regras de negócio

- R1: <regra testável>

## Cenários

<os cenários Gherkin do card, ajustados com as respostas da entrevista>

## Critérios de aceite

- [ ] <critério do card>

## Fora do escopo

- <o que este card não faz, e qual card faz, se houver>

## Decisões

- <pergunta> → <resposta> (<quem decidiu>)

## Suposições

- <o que você assumiu sem perguntar, e por quê>
```

- Toda decisão que você tomou sozinho vai em **Suposições**. É isso que a pessoa está aprovando de verdade.
- Ponto que ninguém respondeu fica marcado no texto como `[PRECISA ESCLARECER: <pergunta>]`. O `/slot-plan` não começa enquanto houver uma marca dessas.

Deixe `Aprovado por:` vazio. Só uma pessoa do time preenche.

Commite com o `/slot-commit` (`SLOT-N: (docs) adiciona a spec`).

## Fim

Duas linhas: a branch criada e o caminho da spec. Peça para alguém do time ler a spec e preencher `Aprovado por: <nome>`. O próximo passo é `/slot-plan`, em uma sessão nova.
