# SLOT-11 · Monitor publica horários

Card: https://slotgestao.atlassian.net/browse/SLOT-11
Tipo: História · Branch: `feature/SLOT-11-publicar-horarios` · Base: `develop`
Aprovado por: Adrian Felipe Bandeira da Silva

## Objetivo

Permitir que monitores publiquem e gerenciem a disponibilidade de atendimento das suas turmas. Os horários publicados alimentam a consulta e a reserva de vagas das histórias SLOT-14 e SLOT-15.

## Regras de negócio

- Só um monitor vinculado à turma pode publicar horários nela. Uma tentativa sem vínculo retorna 403.
- Cada horário tem turma, monitor, início, fim, local e pelo menos uma vaga. O fim deve ser posterior ao início, e o início deve estar no futuro.
- O monitor pode publicar um horário único ou N ocorrências semanais, incluindo a primeira.
- Os horários ativos do mesmo monitor não podem se sobrepor, mesmo em turmas diferentes. A regra vale para publicação, repetição e edição.
- O monitor pode editar um horário próprio sem agendamentos e cancelar qualquer horário próprio. Havendo agendamentos, o cancelamento também deve cancelá-los e avisar os alunos, conforme a integração prevista com a SLOT-18.
- Remover o vínculo de um monitor cancela os horários futuros dele naquela turma, conforme o backlog e a spec da SLOT-10.
- O local aceita texto livre ou link online enquanto o catálogo de salas da SLOT-21 estiver pendente.

## Cenários

```gherkin
Cenário: Publicar um horário
  Dado que sou monitor de IMD0030 T01
  Quando publico um horário na terça das 14:00 às 15:00 na sala "CIVT A204" com 3 vagas
  Então o horário fica disponível para IMD0030

Cenário: Horários sobrepostos
  Dado que já tenho um horário na terça das 14:00 às 15:00
  Quando publico outro horário na terça das 14:30 às 15:30
  Então vejo "Este horário se sobrepõe a outro horário seu"

Cenário: Horário no passado
  Quando publico um horário que começa antes de agora
  Então vejo "O horário precisa começar no futuro"

Cenário: Não é monitor
  Dado que não sou monitor de nenhuma turma de IMD0040
  Quando tento publicar um horário para IMD0040
  Então recebo um erro 403
```

## Critérios de aceite

- [ ] O monitor informa turma, início, fim, local e vagas e visualiza os horários publicados.
- [ ] A publicação semanal cria exatamente N horários.
- [ ] Horários sobrepostos do mesmo monitor são rejeitados.
- [ ] O monitor pode editar um horário sem agendamentos e cancelar qualquer horário próprio.
- [ ] A remoção do monitor cancela seus horários futuros naquela turma.
- [ ] As telas desktop e mobile seguem os protótipos anexados ao Jira.

## Dependências

- SLOT-10 fornece o vínculo entre monitor e turma.
- SLOT-14 e SLOT-15 implementam a consulta pelo aluno e a reserva de vagas.
- SLOT-18 implementa as notificações por e-mail de agendamento.
- SLOT-21 introduz o catálogo de salas.

## Suposições para aprovação

- A repetição tem limite de 52 semanas.
- Uma série é publicada por inteiro. Se alguma ocorrência tiver conflito, nenhuma é criada.
- Horários consecutivos podem compartilhar o instante de fim e início.
- Edição e cancelamento aplicam-se à ocorrência selecionada.
- A entrega desta história cobre a gestão de horários pelo monitor. A busca do aluno, a reserva e os e-mails entram nas respectivas histórias.
