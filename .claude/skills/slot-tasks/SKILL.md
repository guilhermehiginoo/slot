---
name: slot-tasks
description: Terceiro passo do SDD do Slot. Quebra o plano do card da branch atual em passos do tamanho de um commit, em specs/SLOT-N/tasks.md.
disable-model-invocation: true
---

# /slot-tasks

Você quebra o plano em passos pequenos, na ordem em que precisam ser feitos. Cada passo vira um commit do `/slot-next`.

## 1. Leia

- Tire a chave `SLOT-N` do nome da branch atual.
- Leia `specs/SLOT-N/spec.md` (precisa estar aprovada) e `specs/SLOT-N/plan.md`. Sem plano, só siga se o card pulou o plano de propósito (Chore pequena ou Hotfix).

## 2. Escreva `specs/SLOT-N/tasks.md`

Até 4 KB.

```markdown
# Tasks · SLOT-N

- [ ] T1 · <o que o passo entrega, em uma frase>
  - Teste: `tests/Feature/<Dominio>/<Classe>::<metodo>` (Cenário: <nome>)
  - Arquivos: `<caminhos principais>`
- [ ] T2 · ...
```

Regras de cada passo:

- Entrega um comportamento que um teste prova. Migration e model sozinhos não são passo: entram no primeiro passo que precisa deles.
- Cabe em um commit e deixa os testes verdes.
- Diz qual teste escrever primeiro (TDD). Passo de Bugfix começa pelo teste que reproduz o bug.
- Ordem: backend antes da tela que depende dele.
- O último passo é sempre conferir a tela no celular (375px), se o card tiver tela.

Todo cenário da spec aparece em pelo menos um passo. Se passar de uns 8 passos, o card provavelmente é grande demais: avise o usuário e sugira como quebrar o card no Jira antes de seguir.

Commite com o `/slot-commit` (`SLOT-N: (docs) adiciona as tasks`).

## Fim

Uma linha com o número de passos. O próximo passo é `/slot-next`.
