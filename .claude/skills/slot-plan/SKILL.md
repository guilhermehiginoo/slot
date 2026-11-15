---
name: slot-plan
description: Segundo passo do SDD do Slot. Lê a spec aprovada do card da branch atual e escreve specs/SLOT-N/plan.md dizendo como construir.
disable-model-invocation: true
---

# /slot-plan

Você decide **como** construir o que a spec pede, seguindo a arquitetura do AGENTS.md. Você não escreve código de produto.

## 1. Confira se pode começar

- Tire a chave `SLOT-N` do nome da branch atual. Sem chave, pare: rode `/slot-specify` primeiro.
- Leia `specs/SLOT-N/spec.md`. Se a linha `Aprovado por:` estiver vazia, pare e peça a aprovação de alguém do time. Não planeje em cima de spec não aprovada. Se houver `[PRECISA ESCLARECER: ...]`, pare e leve a pergunta ao usuário.
- Chore pequena ou Hotfix com uma mudança óbvia pode pular o plano: diga isso e mande direto para `/slot-tasks`.

## 2. Leia antes de decidir

Leia o código que já existe e que o card vai tocar: models, migrations, controllers, rotas, páginas e componentes. Reaproveitar vem antes de criar. Se a spec tiver uma lacuna que muda o plano, pare e pergunte; não invente regra de negócio.

## 3. Escreva `specs/SLOT-N/plan.md`

Até 8 KB. Só as seções que se aplicam ao card.

```markdown
# Plano · SLOT-N

## Banco
- Migration `<nome>`: colunas, tipos, chaves estrangeiras, índices e `unique` (diga qual regra cada `unique` protege)
- Model `<Nome>`: fillable, casts, relações, scopes, enum usado

## Backend
| Rota | Controller@método | FormRequest | Action | Policy |
| --- | --- | --- | --- | --- |

- Action `<Verbo><Coisa>`: o que faz, se usa transação, o que pode dar errado

## Queries
- <página ou endpoint>: quais relações carregar com `with()`, contagens com `withCount()`, paginação e colunas enviadas ao Inertia

## Frontend
- `pages/<dominio>/<pagina>.tsx`: props que recebe e o que mostra
- `components/<dominio>/<componente>.tsx`: novo ou reaproveitado
- Comportamento no celular (375px)

## Testes
| Cenário da spec | Teste |
| --- | --- |
| <nome do cenário> | `tests/Feature/<Dominio>/<Classe>::<metodo>` |

## Riscos
- <o que pode quebrar e como o plano evita>
```

Toda regra de negócio da spec (R1, R2...) aparece em algum lugar do plano, e todo cenário tem uma linha na tabela de testes.

Commite com o `/slot-commit` (`SLOT-N: (docs) adiciona o plano`).

## Fim

Uma linha com o caminho do plano. O próximo passo é `/slot-tasks`, em uma sessão nova.
