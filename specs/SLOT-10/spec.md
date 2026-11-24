# SLOT-10 · Professor convida e remove monitores

Card: https://slotgestao.atlassian.net/browse/SLOT-10
Tipo: História · Branch: `feature/SLOT-10-convidar-remover-monitores` · Base: `develop`
Aprovado por:

## Objetivo

O professor controla quem dá monitoria nas turmas em que dá aula: convida alunos, revoga convites e remove monitores. Como virar monitor torna o perfil do aluno público (SLOT-23), o aluno precisa aceitar o convite antes. As ações entram na página de turma do professor criada na SLOT-9.

## Regras de negócio

- R1: Só o professor da turma convida, revoga convite e remove monitor dela.
- R2: Só usuários com papel aluno aparecem na busca de candidatos e podem ser convidados.
- R3: Só um convite pendente por aluno por turma. Quem já é monitor da turma não pode ser convidado.
- R4: O convite nasce `pending` e vai para `accepted`, `declined` ou `revoked`. Só convite pendente muda de status.
- R5: O professor pode revogar um convite pendente.
- R6: Só o aluno convidado aceita ou recusa o convite.
- R7: Ao aceitar, o sistema confere que o convite ainda está pendente e cria a linha em `class_monitor` (`appointed_by` = quem convidou, `appointed_at` = agora) na mesma transação.
- R8: O aluno vê os convites pendentes no dashboard e recebe um e-mail em fila quando é convidado.
- R9: Um aluno pode ser monitor de mais de uma turma.
- R10: Remover um monitor apaga a linha de `class_monitor` depois de o professor confirmar num diálogo.
- R11: Um convite recusado ou revogado não impede um convite novo.

## Cenários

```gherkin
Cenário: Convidar um monitor
  Dado que estou na página de uma turma em que dou aula
  Quando busco o aluno João e o convido
  Então o convite aparece como "Pendente" na página da turma
  E o João ainda não é monitor

Cenário: O aluno aceita
  Dado que o João tem um convite pendente para IMD0030 T01
  Quando o João aceita
  Então o João vira monitor de IMD0030 T01

Cenário: O aluno recusa
  Quando o João recusa o convite
  Então o professor vê "Recusado"
  E o João não é monitor da turma

Cenário: Convite repetido
  Dado que o João tem um convite pendente para IMD0030 T01
  Quando convido o João de novo
  Então vejo "O João já tem um convite pendente para esta turma"

Cenário: Só alunos podem ser monitores
  Quando busco um professor ou um admin
  Então eles não aparecem como candidatos

Cenário: Revogar um convite
  Dado que o João tem um convite pendente
  Quando revogo o convite
  Então o convite aparece como "Revogado" e o João não pode mais aceitá-lo

Cenário: Remover um monitor
  Dado que o João é monitor de IMD0030 T01
  Quando removo o João e confirmo
  Então o João deixa de ser monitor da turma
```

## Critérios de aceite

- [ ] Usa a pivô `class_monitor` e a tabela de convites já criadas na SLOT-9; a linha da pivô só nasce no aceite.
- [ ] Status do convite `pending | accepted | declined | revoked`; professor revoga convite pendente.
- [ ] Aluno vê convites pendentes no dashboard e recebe e-mail em fila.
- [ ] Um convite pendente por aluno por turma; monitor atual não pode ser convidado.
- [ ] Aceite confere o status e cria a pivô na mesma transação.
- [ ] Aluno pode ser monitor de várias turmas.
- [ ] Toda rota passa por Policy; tela funciona com 375px.

## Fora do escopo

- Cancelar atendimentos futuros ao remover monitor e o aviso disso (fica para a SLOT-11, que cria os horários).
- Monitor aparecer na busca pública da disciplina (SLOT-23).
- Solicitação de monitoria e aprovação sem convite (SLOT-19).

## Decisões

- Usuário pediu para seguir sem perguntas; as decisões abaixo estão em Suposições. (Guilherme)

## Suposições

- Os cenários de "aparece na busca da disciplina" e "atendimentos futuros cancelados" dependem da SLOT-23 e da SLOT-11, que ainda não existem; ficaram fora e a remoção hoje só confirma e apaga a pivô.
- A busca de candidatos é por nome ou e-mail, dentro do diálogo "Convidar aluno" da página da turma, paginada/limitada.
- O dashboard do aluno ganha um bloco "Convites de monitoria" com aceitar e recusar; sem página separada.
- O e-mail é só para o convite; aceite, recusa e revogação não geram e-mail.
- Convite aceito/recusado/revogado continua listado para o professor com o status; o histórico não é apagado.
- Status em português na tela: Pendente, Aceito, Recusado, Revogado.
- Não há prazo de expiração do convite.
