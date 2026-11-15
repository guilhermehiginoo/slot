---
name: slot-review
description: Último passo do SDD do Slot. Confere card, spec, plano, tasks e o código do card da branch atual. Problemas voltam como passos novos no tasks.md; sem problemas, abre o PR.
disable-model-invocation: true
---

# /slot-review

Rode em uma sessão nova: você não escreveu o código que vai julgar. Você não corrige nada: aponta, e o `/slot-next` corrige.

## 1. Leia tudo

- Tire a chave `SLOT-N` do nome da branch atual e a base (`main` para `hotfix/`, `develop` para o resto).
- O card no Jira (conector do Atlassian, `getJiraIssue`) ou o link na spec.
- `specs/SLOT-N/spec.md`, `plan.md` e `tasks.md`.
- O diff inteiro: `git fetch origin && git diff origin/<base>...HEAD`. Leia também o código em volta das mudanças, não só as linhas alteradas.

## 2. Confira

**Contra a spec**

- Todo critério de aceite está cumprido.
- Todo cenário tem um teste com `#[TestDox('Cenário: ...')]` que realmente prova o cenário. Procure com `grep -rn "Cenário:" tests/`.
- Nada fora do escopo do card entrou no diff.
- Todo passo do `tasks.md` está marcado.

**Backend**

- Controller só autoriza, valida, chama a Action e responde. Validação em FormRequest, regra de negócio em Action, autorização em Policy.
- Toda rota que lê ou altera dado de alguém tem autorização, e existe teste de acesso negado.
- Sem N+1: relações com `with()`, sem query em loop ou accessor de listagem, listagens paginadas, props do Inertia só com os campos usados.
- Migrations com chave estrangeira, índices e `unique` onde a regra exige.
- Escrita em mais de uma tabela dentro de transação.

**Frontend**

- Arquivos no lugar certo (`pages/<dominio>/`, `components/<dominio>/`), kebab-case, um componente por arquivo.
- URLs pelo Wayfinder, sem string solta. Sem `any` e sem `@ts-ignore`.
- Componentes existentes reaproveitados.

**Rodando**

- `composer test` verde.
- Abra cada página nova ou alterada em `http://slot.test` com 375px de largura e confira se nada quebra ou estoura a tela. Use o `insights` do Herd para ver se a página faz N+1 ou query lenta.

## 3. Registre

Escreva `specs/SLOT-N/review.md` (até 4 KB):

```markdown
# Review · SLOT-N

Veredito: aprovado | voltar para o /slot-next

## Problemas

- <arquivo:linha> · <o que está errado> · <por que importa>

## Conferido

- <o que você verificou e passou>
```

Para cada problema, adicione um passo novo no fim do `tasks.md`: `- [ ] T<n> · Review: <o que corrigir>`, com o teste que pega o problema. Commite com o `/slot-commit` (`SLOT-N: (docs) registra o review`).

## 4. Termine

**Com problemas:** diga quantos são e que o próximo passo é `/slot-next` até acabarem, e depois `/slot-review` de novo, em uma sessão nova.

**Sem problemas:** peça confirmação ao usuário antes de publicar. Com o sim:

```bash
git push -u origin HEAD
gh pr create --base <base> --title "SLOT-N: <título do card>" --body-file <arquivo>
```

O corpo segue `.github/pull_request_template.md`, preenchido com o link do card, os critérios de aceite marcados e um resumo do que mudou. Se o conector do Atlassian estiver disponível, mova o card para **Revisando PR**. Termine com o link do PR.
