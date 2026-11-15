---
name: slot-next
description: Quarto passo do SDD do Slot. Implementa com TDD o próximo passo não marcado de specs/SLOT-N/tasks.md, roda os testes e commita.
disable-model-invocation: true
---

# /slot-next

Você implementa **um** passo do `tasks.md` por vez, com TDD, e commita. Siga o AGENTS.md à risca: controller fino, sem N+1, organização do frontend.

## 1. Ache o passo

- Tire a chave `SLOT-N` do nome da branch atual. Sem chave, diga para rodar `/slot-specify SLOT-N`.
- Leia `specs/SLOT-N/tasks.md` e pegue o primeiro passo `- [ ]`.
- Sem passo pendente, diga em que etapa o card está e qual comando rodar:

| Situação                 | Próximo comando                    |
| ------------------------ | ---------------------------------- |
| Sem `spec.md`            | `/slot-specify SLOT-N`             |
| Spec sem `Aprovado por:` | pedir aprovação a alguém do time   |
| Sem `plan.md`            | `/slot-plan`                       |
| Sem `tasks.md`           | `/slot-tasks`                      |
| Todos os passos marcados | `/slot-review`, em uma sessão nova |

- Leia a spec, o plano e o código que o passo toca. Nada além do passo: o que for de outro passo ou de outro card fica de fora.

## 2. Vermelho

Escreva o teste que o passo indica, com `#[TestDox('Cenário: <nome>')]` quando cobrir um cenário da spec. Rode só ele:

```bash
php artisan test --filter=<metodo>
```

Ele precisa falhar, e pelo motivo certo. Se passou de primeira, o teste não prova nada: corrija o teste antes de seguir.

## 3. Verde

Escreva o mínimo de código para o teste passar. Rode o teste de novo até ficar verde.

## 4. Refatore e confira

Limpe o código com o teste verde. Depois rode tudo:

```bash
composer test
```

Isso roda Pint, PHPStan e todos os testes. Se o Pint reclamar, rode `./vendor/bin/pint`. Só siga com tudo verde. Se o modo estrito acusar lazy loading, corrija com `with()`.

## Quando parar

Pare e explique, sem improvisar, quando:

- a spec não fecha com o código ou com outra regra: o usuário precisa atualizar a spec;
- o passo precisa de algo que o plano não previu, como uma tabela ou rota nova;
- você não consegue deixar os testes verdes depois de duas tentativas honestas.

## 5. Marque e commite

Troque o `- [ ]` do passo por `- [x]` no `tasks.md` e commite com o `/slot-commit`, incluindo o `tasks.md` no mesmo commit.

## Fim

Duas linhas: o passo feito e o commit. Diga quantos passos faltam e que o próximo é `/slot-next` de novo (ou `/slot-review` se acabaram).
