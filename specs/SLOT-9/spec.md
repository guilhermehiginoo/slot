# SLOT-9 · Página de turmas do professor

Card: https://slotgestao.atlassian.net/browse/SLOT-9
Tipo: História · Branch: `feature/SLOT-9-turmas-do-professor` · Base: `develop`
Aprovado por:

## Objetivo

O professor vê as turmas em que dá aula no semestre atual e, em cada turma, quem são os monitores, quem foi convidado e quem pediu para ser monitor. É a base da gestão de monitores: a SLOT-10 (convidar e remover) e a SLOT-19 (solicitar) adicionam as ações nesta página.

## Regras de negócio

- R1: só usuários com papel professor abrem "Minhas turmas"; admin e aluno recebem 403.
- R2: "Minhas turmas" lista só as turmas do semestre atual (`SLOT_CURRENT_SEMESTER`) em que o professor dá aula, ordenadas pelo código da disciplina e depois pelo número, de 15 em 15.
- R3: cada turma da lista mostra disciplina ("IMD0030 · Linguagem de Programação I"), número, horário, quantidade de monitores atuais e o link "Gerenciar turma".
- R4: a página da turma só abre para quem tem papel professor e dá aula nela; qualquer outra pessoa recebe 403, inclusive admin e outro professor. Vale para turmas de qualquer semestre.
- R5: a página da turma mostra "IMD0030 · T01", o nome da disciplina, o semestre, o horário e os professores.
- R6: "Monitores atuais" lista os alunos que são monitores da turma, em ordem alfabética, com a data em que viraram monitores.
- R7: "Convites pendentes" lista os convites de monitoria com status pendente, do mais recente para o mais antigo, com o aluno e a data do convite. Convites aceitos, recusados e revogados não aparecem.
- R8: "Solicitações pendentes" lista as solicitações de monitoria com status pendente, da mais antiga para a mais recente, com o aluno, a mensagem (se houver) e a data.
- R9: o professor só lê. Não há ação nesta página neste card.
- R10: o seeder põe dois monitores, um convite pendente e uma solicitação pendente em IMD0030 T01, sem duplicar ao rodar de novo.

## Cenários

```gherkin
Cenário: O professor vê só as próprias turmas
  Dado que dou aula em IMD0030 T01 e não em IMD0030 T02
  Quando abro "Minhas turmas"
  Então vejo IMD0030 T01
  E não vejo IMD0030 T02

Cenário: Professor abre a turma de outro professor
  Quando abro a página de gestão de IMD0030 T02 pela URL
  Então recebo um erro 403
```

## Tela (protótipo)

- **Minhas turmas:** título "Minhas turmas", subtítulo "Semestre 2026.2 · Apenas as turmas em que você leciona.". Colunas Disciplina, Turma, Horário, Monitores e Ações ("Gerenciar turma"). Rodapé da paginação "Exibindo X–Y de Z turmas". No celular, um card por turma com "Gerenciar turma" na largura toda. Sem turma: "Nenhuma turma neste semestre" e "As turmas em que você dá aula aparecem aqui quando o admin as cadastrar.".
- **Página da turma:** título "IMD0030 · T01", subtítulo "Linguagem de Programação I · 2026.2 · 35M12", selos com os nomes dos professores e o semestre. Três seções em tabela no desktop e cards no celular:
    - "Monitores atuais": Aluno e Monitor desde. Vazia: "Nenhum monitor nesta turma.".
    - "Convites pendentes": Aluno, Enviado em e Status ("Pendente"). Vazia: "Nenhum convite pendente.".
    - "Solicitações pendentes": Aluno, Mensagem e Enviada em. Vazia: "Nenhuma solicitação pendente.".
- **Menu:** item "Minhas turmas" no grupo "Acadêmico", só para professor.

## Critérios de aceite

- [ ] "Minhas turmas" lista as turmas do semestre atual em que o professor dá aula, com a quantidade de monitores.
- [ ] A página da turma lista os monitores atuais, os convites pendentes e as solicitações de monitoria pendentes.
- [ ] Uma policy bloqueia o acesso às turmas em que o professor não dá aula.
- [ ] As telas funcionam com 375px de largura.

## Fora do escopo

- Convidar, remover monitor e revogar convite, e o aceite do aluno (SLOT-10).
- Solicitar ser monitor, aprovar e recusar (SLOT-19).
- O item "Convites de monitoria" do menu do protótipo (SLOT-10, lado do aluno).
- Total de presenças por monitor (SLOT-28).

## Decisões

- O card exige listar monitores, convites e solicitações, mas as tabelas são da SLOT-10 e da SLOT-19. Este card cria as três (`class_monitor`, convites e solicitações) com as colunas do backlog, só para leitura. A SLOT-10 e a SLOT-19 usam essas tabelas e adicionam as ações. (Claude, sem resposta na entrevista)
- Os botões do protótipo ("Convidar aluno", "Remover monitor", "Revogar convite") ficam para a SLOT-10, para não haver botão sem ação. (Claude, sem resposta na entrevista)

## Suposições

- URLs `/professor/turmas` e `/professor/turmas/{turma}`, em português como as demais.
- Admin não abre a página da turma: o card fala só do professor, e o admin já tem a lista de turmas.
- Um professor que mudou de papel continua ligado às turmas (SLOT-8), mas perde o acesso pela R1/R4.
- As listas da página da turma não são paginadas: são poucas pessoas por turma.
- Do protótipo ficam de fora: "Prof.ª" e "Ativo/Ativa" (o sistema não sabe o gênero), a coluna Vínculo (repete o título), a coluna Status dos monitores (todo monitor atual está ativo), o convite "Recusado" (o card pede só os pendentes), o menu "Espaço do professor" e o rodapé "1 turma encontrada no semestre atual." (fica a paginação, como na SLOT-8).
- A solicitação tem mensagem opcional de até 500 caracteres; o motivo da recusa fica para a SLOT-19.
- Alunos do seeder: João Silva e Marina Santos (monitores), Lucas Almeida (convite) e Beatriz Lima (solicitação), todos `@slot.test`.
