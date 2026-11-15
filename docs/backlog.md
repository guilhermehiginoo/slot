# Slot – Backlog

O Slot é um sistema independente de agendamento de monitorias para o IMD/UFRN. O aluno encontra monitorias pela disciplina, agenda um horário com um monitor, tira dúvidas pelo chat do próprio sistema, e monitores e professores reservam salas do IMD. Não há integração com o SIGAA nem com nenhum sistema da universidade.

O texto das tasks e da documentação é em português. O código (nomes de classes, colunas, rotas, enums) é em inglês e aparece entre crases. Mensagens de commit seguem `SLOT-N: (tipo) frase em português`, como manda o AGENTS.md.

## Como o IMD funciona (o que modelamos)

- **Disciplina (componente curricular, `Course` no código)**: identificada por um código de três letras e quatro dígitos, como `IMD0030` ou `DIM0115`, e um nome. O código não muda de um semestre para outro.
- **Turma (`ClassGroup` no código, porque `class` é palavra reservada do PHP)**: uma disciplina ofertada em um semestre (`2026.2`), com um número (`T01`), um ou mais professores, um código de horário do SIGAA (`35M12` quer dizer terça e quinta, manhã, 1º e 2º horários) e uma sala. Uma disciplina tem várias turmas por semestre.
- **Monitoria na UFRN**: o professor submete um projeto de monitoria à PROGRAD e seleciona os monitores, que podem ser bolsistas ou voluntários. O Slot não modela o projeto nem a seleção, só o resultado: o professor marca um aluno como monitor da sua turma.
- **Prédios**: a sede do IMD é o prédio do CIVT (Av. Senador Salgado Filho, 3000). O instituto também usa os prédios do nPITI e do BioME. As fontes oficiais falam em cerca de 137 salas e 21 laboratórios, mas não existe uma lista pública por sala. Vamos ter que fazer o levantamento nós mesmos (SLOT-12).

## Papéis

| Papel | O que faz |
| --- | --- |
| **Admin** | Gerencia usuários, papéis, disciplinas, turmas e salas. |
| **Professor** | Vê as próprias turmas, convida e remove monitores, aprova solicitações de monitoria. |
| **Aluno** | Busca disciplinas, professores e monitores, agenda horários, conversa pelo chat, solicita ser monitor. |
| **Monitor** | Não é um papel separado: é um aluno vinculado a uma **turma**. Publica horários e faz os atendimentos da disciplina daquela turma. |

Regras que valem para o backlog inteiro:
- O aluno nunca se declara monitor sozinho. Ele solicita e o professor da turma aprova, ou o professor convida e ele aceita. Um aluno nunca fica público como monitor sem o próprio consentimento.
- O professor é dono da **turma**, mas o aluno procura ajuda pela **disciplina**. Uma busca por `IMD0030` mostra os monitores de todas as turmas de IMD0030 no semestre atual.
- Não existe matrícula. Como não integramos com o SIGAA, qualquer aluno pode agendar monitoria de qualquer disciplina.

## Calendário das sprints

As sprints duram uma semana e terminam na terça-feira.

| Sprint | Datas | Objetivo |
| --- | --- | --- |
| 1 | 10/11 – 17/11 | Base do projeto e papéis de usuário |
| 2 | 17/11 – 24/11 | O monitor consegue publicar horários para uma turma |
| 3 | 24/11 – 01/12 | O aluno encontra uma disciplina e agenda um atendimento |
| 4 | 01/12 – 08/12 | Chat sem WhatsApp, salas do IMD e entrega |

## Tipos de ticket

Cada tipo do Jira corresponde a um prefixo de branch do AGENTS.md, e a branch leva a chave do card: `feature/SLOT-12-reservar-horario`. A linha logo abaixo do título de cada item indica o tipo.

| Tipo no Jira | Branch | Quando usar |
| --- | --- | --- |
| História | `feature/` | O usuário passa a conseguir fazer algo que antes não conseguia. |
| Enhancement | `enhancement/` | Algo que já funciona fica melhor, e o usuário percebe. |
| Bugfix | `bugfix/` | Bug que pode esperar o próximo deploy. |
| Hotfix | `hotfix/` | Bug urgente em produção. Sai da `main` e o PR vai para a `main`. |
| Chore | `chore/` | Manutenção que termina em código, invisível para o usuário. |
| Tarefa | nenhuma | Trabalho sem código: levantamento, apresentação. |

## Prioridade

Todo item tem uma prioridade MoSCoW, definida pelo valor que ele agrega à promessa principal: **o aluno encontra ajuda para uma disciplina e agenda um atendimento**.

| Prioridade | Significado | Prioridade no Jira |
| --- | --- | --- |
| **Must** | Sem ele, o fluxo principal ou a entrega não existe. | High |
| **Should** | Agrega muito, mas a plataforma funciona sem ele. | Medium |
| **Could** | Bom de ter. É o primeiro a ser cortado. | Low |

Dentro de cada sprint, os itens estão em ordem de prioridade. Se faltar tempo, corta-se primeiro o Could e depois o Should. Um Must só sai da sprint se o time concordar na daily.

## Estimativa

Story points na escala de Fibonacci: 1, 2, 3, 5, 8. Os pontos medem esforço, complexidade e incerteza juntos, em relação aos outros itens, e não horas. Qualquer item estimado em 13 ou mais é quebrado em itens menores antes de entrar na sprint (foi assim que a SLOT-20 virou SLOT-20 e SLOT-29). A velocidade é a soma dos pontos dos itens concluídos ao fim da sprint.

| Sprint | Pontos |
| --- | --- |
| 1 | 20 |
| 2 | 29 |
| 3 | 26 |
| 4 | 49 |

## Definição de pronto (vale para todo item)

- Testes de feature cobrem os cenários listados no item.
- `composer ci:check` passa (Pint, PHPStan, testes e tipos do TypeScript).
- Entrou na `develop` por um PR revisado, usando o prefixo de branch indicado no item.
- A autorização é feita no servidor (policies e middleware), e não só escondendo a interface.
- **Responsivo, mobile first.** Toda tela funciona em um celular de 375px de largura e no desktop, sem rolagem horizontal. Listas viram cards no mobile, e as áreas de toque têm pelo menos 44px. Conferido no modo de dispositivo do navegador antes do PR.

---

## Sprint 1 – Base do projeto (10/11 – 17/11)

### SLOT-1 · Criar projeto Laravel com o starter kit de React ✅
`chore/` · Concluída · **Prioridade:** Must · **Pontos:** 3

Instalar o Laravel 13 com o starter kit oficial de React e Inertia, autenticação com Fortify e SQLite no ambiente local. Remover autenticação em dois fatores e passkeys (fora do escopo).

**Critérios de aceite**
- [x] O app roda localmente em `http://slot.test`.
- [x] Cadastro, login, logout, redefinição de senha e verificação de e-mail funcionam.
- [x] 2FA e passkeys não aparecem na interface nem nas rotas.

### SLOT-2 · Ferramentas de qualidade de código ✅
`chore/` · Concluída · **Prioridade:** Must · **Pontos:** 2

Configurar o Laravel Pint e o PHPStan com Larastan.

**Critérios de aceite**
- [x] `./vendor/bin/pint --test` e `./vendor/bin/phpstan analyse` passam sem erros na `main`.

### SLOT-3 · Git hooks e regras de branch ✅
`chore/` · Concluída · **Prioridade:** Must · **Pontos:** 3

Hook `pre-push` do Husky que valida o nome das branches e roda Pint e PHPStan. Workflow `branch-name` no GitHub que valida o nome e o destino de cada PR. Branches do Dependabot são aceitas.

**Critérios de aceite**
- [x] O push de `fix/foo` é bloqueado localmente com uma mensagem clara.
- [x] Um PR de `feature/*` para a `main` falha no check `branch-name`.
- [x] Um PR de `dependabot/*` para a `develop` passa.

### SLOT-4 · Pipeline de CI e documentação do projeto ✅
`chore/` · Concluída · **Prioridade:** Must · **Pontos:** 2

Workflow `tests` em todo PR. README com instruções de instalação e AGENTS.md com as regras de branch.

**Critérios de aceite**
- [x] Uma pessoa nova no time consegue clonar e rodar o app seguindo só o README.
- [x] O CI roda em todo PR para a `develop` e para a `main`.

### SLOT-5 · Configurar o fluxo SDD do Slot (comandos /slot-*)
`chore/` · **Prioridade:** Must · **Pontos:** 5

Montar um fluxo de Spec-Driven Development próprio do Slot, inspirado no [speckit](https://github.com/VictorL0pes/speckit) mas mais enxuto: seis comandos do Claude Code em `.claude/skills/`, a constituição no `AGENTS.md` e validações no Git para garantir os padrões de branch e commit.

Fluxo: `/slot-specify SLOT-N` → aprovação da spec → `/slot-plan` → `/slot-tasks` → `/slot-next` (um por passo, com TDD, chamando `/slot-commit`) → `/slot-review`. Os artefatos de cada card ficam em `specs/SLOT-N/`.

**Cenários**

```gherkin
Cenário: A branch sai do tipo e da chave do card
  Dado o card SLOT-12 do tipo História
  Quando alguém do time roda /slot-specify SLOT-12
  Então a branch feature/SLOT-12-<descricao> é criada a partir da develop
  E a spec é salva em specs/SLOT-12/spec.md

Cenário: Nenhum código antes da spec ser aprovada
  Dado uma spec com a linha "Aprovado por:" vazia
  Quando alguém do time roda /slot-plan
  Então o comando se recusa a começar e pede a aprovação

Cenário: Commit com a chave errada é bloqueado
  Dado que estou na branch chore/SLOT-5-configurar-sdd
  Quando tento commitar com a mensagem "SLOT-7: (docs) ajusta texto"
  Então o hook commit-msg recusa o commit

Cenário: Commit com coautoria de IA é bloqueado
  Dado uma mensagem com a linha "Co-Authored-By: Claude"
  Quando tento commitar
  Então o hook commit-msg recusa o commit

Cenário: Push com nome de branch fora do padrão é bloqueado
  Dado a branch feature/12-reservar-horario
  Quando tento fazer push
  Então o pre-push recusa e mostra o formato prefixo/SLOT-N-descricao
```

**Critérios de aceite**
- [ ] `AGENTS.md` como constituição: idioma, fluxo SDD, TDD, branches com a chave do Jira, padrão de commit sem coautoria de IA, controller fino, N+1 e performance, organização do frontend e definição de pronto. O `CLAUDE.md` continua importando o `AGENTS.md`.
- [ ] Comandos `/slot-specify`, `/slot-plan`, `/slot-tasks`, `/slot-next`, `/slot-commit` e `/slot-review` em `.claude/skills/`.
- [ ] Hook `commit-msg` exige `SLOT-N: ` igual à chave da branch e bloqueia coautoria de IA.
- [ ] Hook `pre-push` e workflow `branch-name` exigem `prefixo/SLOT-N`; o `pre-push` roda Pint, PHPStan e os testes.
- [ ] `Model::shouldBeStrict()` ligado fora de produção.
- [ ] Template de PR e `.claude/settings.json` sem atribuição de coautoria.
- [ ] README com o board do Jira, as sprints e o fluxo SDD.
- [ ] GitHub conectado ao Jira, para branches, commits e PRs aparecerem no card.
- [ ] A SLOT-6 passa pelo fluxo inteiro como card piloto.

### SLOT-6 · Papéis de usuário
`feature/` · **Prioridade:** Must · **Pontos:** 5

Como **admin**, quero que cada usuário tenha um papel (admin, professor, aluno), para que cada pessoa só veja e faça o que cabe a ela.

Quem se cadastra entra como aluno. Só um admin muda o papel de alguém. Os seeders criam um usuário de cada papel para o desenvolvimento.

**Cenários**
```gherkin
Cenário: Novo cadastro entra como aluno
  Dado que estou na página de cadastro
  Quando crio uma conta
  Então meu papel é "aluno"

Cenário: Quem não é admin não acessa as páginas de admin
  Dado que estou logado como aluno
  Quando abro /admin/users
  Então recebo um erro 403

Cenário: Admin muda um papel
  Dado que estou logado como admin
  Quando mudo o papel da Maria para "professor"
  Então a Maria vê o menu de professor na próxima requisição
```

**Critérios de aceite**
- [ ] Coluna `role` em `users` com um enum PHP (`admin`, `professor`, `student`), padrão `student`.
- [ ] Middleware ou gate restringe as rotas de admin e devolve 403 para os demais.
- [ ] Página de usuários do admin com busca por nome ou e-mail e um seletor de papel.
- [ ] O admin não consegue tirar o próprio papel de admin (evita ficar sem acesso).
- [ ] O `DatabaseSeeder` cria `admin@slot.test`, `professor@slot.test` e `student@slot.test`.
- [ ] Uma prop compartilhada do Inertia expõe o papel do usuário logado, e o menu lateral mostra os itens de cada papel.

---

## Sprint 2 – O monitor consegue publicar horários (17/11 – 24/11)

### SLOT-7 · Catálogo de disciplinas
`feature/` · **Prioridade:** Must · **Pontos:** 3

Como **admin**, quero cadastrar as disciplinas com o código e o nome da UFRN, para que turmas e monitorias apontem para a disciplina certa.

**Cenários**
```gherkin
Cenário: Admin cria uma disciplina
  Dado que estou logado como admin
  Quando crio a disciplina "IMD0030" com o nome "Linguagem de Programação I"
  Então ela aparece no catálogo de disciplinas

Cenário: O código é normalizado
  Quando crio uma disciplina com o código "imd0030 "
  Então ela é salva como "IMD0030"

Cenário: Código inválido
  Quando crio uma disciplina com o código "IMD30"
  Então vejo "O código deve ter 3 letras seguidas de 4 dígitos"

Cenário: Código repetido
  Dado que existe uma disciplina com o código "IMD0030"
  Quando crio outra disciplina com o código "IMD0030"
  Então vejo "Este código já está em uso"
```

**Critérios de aceite**
- [ ] A disciplina tem código (único, `^[A-Z]{3}\d{4}$`, salvo em maiúsculas e sem espaços) e nome.
- [ ] O admin cria, edita e arquiva disciplinas. Disciplinas arquivadas não aparecem nas buscas nem aceitam novos horários, mas mantêm o histórico.
- [ ] Seeder com as principais disciplinas do BTI, para o app não ficar vazio no desenvolvimento.

### SLOT-8 · Turmas
`feature/` · **Prioridade:** Must · **Pontos:** 5

Como **admin**, quero cadastrar as turmas de cada disciplina no semestre, com professores e horário, para que os professores possam gerenciar seus monitores.

**Cenários**
```gherkin
Cenário: Admin cria uma turma
  Dado que a disciplina IMD0030 existe
  Quando crio a turma "T01" do semestre "2026.2" com a professora Ana e o horário "35M12"
  Então a Ana vê IMD0030 T01 em "Minhas turmas"

Cenário: Turma repetida
  Dado que IMD0030 T01 existe em 2026.2
  Quando crio IMD0030 T01 em 2026.2 de novo
  Então vejo "Esta turma já existe neste semestre"

Cenário: Só professores dão aula
  Quando tento colocar um aluno como professor de uma turma
  Então o aluno não aparece como opção
```

**Critérios de aceite**
- [ ] A turma tem disciplina, semestre (`YYYY.1` ou `YYYY.2`), número (`T01`), um ou mais professores, código de horário (texto livre, só para exibição) e uma sala opcional (ligada quando a SLOT-21 existir).
- [ ] Disciplina + semestre + número é único.
- [ ] O semestre atual vem da configuração (`SLOT_CURRENT_SEMESTER`). Buscas e agendamentos só consideram turmas do semestre atual.

### SLOT-9 · Página de turmas do professor
`feature/` · **Prioridade:** Must · **Pontos:** 3

Como **professor**, quero ver minhas turmas e os monitores de cada uma, para saber quem está ajudando meus alunos.

**Cenários**
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

**Critérios de aceite**
- [ ] "Minhas turmas" lista as turmas do semestre atual em que o professor dá aula, com a quantidade de monitores.
- [ ] A página da turma lista os monitores atuais, os convites pendentes e as solicitações de monitoria pendentes.
- [ ] Uma policy bloqueia o acesso às turmas em que o professor não dá aula.

### SLOT-10 · Professor convida e remove monitores
`feature/` · **Prioridade:** Must · **Pontos:** 8

Como **professor**, quero convidar um aluno para ser monitor da minha turma, e removê-lo, para controlar quem dá monitoria para os meus alunos.

Virar monitor torna o nome e o perfil do aluno públicos (SLOT-23), então o aluno precisa aceitar o convite antes.

**Cenários**
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
  E o João aparece como monitor quando alguém busca a disciplina

Cenário: O aluno recusa
  Quando o João recusa o convite
  Então o professor vê "Recusado"
  E o João não aparece como monitor em lugar nenhum

Cenário: Convite repetido
  Dado que o João tem um convite pendente para IMD0030 T01
  Quando convido o João de novo
  Então vejo "O João já tem um convite pendente para esta turma"

Cenário: Só alunos podem ser monitores
  Quando busco um professor ou um admin
  Então eles não aparecem como candidatos

Cenário: Remover um monitor com atendimentos futuros
  Dado que o João tem atendimentos futuros agendados nesta turma
  Quando removo o João da monitoria
  Então sou avisado de que os atendimentos futuros dele serão cancelados
  E depois de confirmar, esses atendimentos são cancelados
```

**Critérios de aceite**
- [ ] Tabela pivô `class_monitor` (turma, aluno, `appointed_by`, `appointed_at`), criada só quando o aluno aceita.
- [ ] O convite tem status `pending | accepted | declined | revoked`. O professor pode revogar um convite pendente.
- [ ] O aluno vê os convites pendentes no dashboard e recebe um e-mail (em fila).
- [ ] Só um convite pendente por aluno por turma. Quem já é monitor da turma não pode ser convidado.
- [ ] Ao aceitar, o sistema confere se o convite ainda está pendente e cria a linha da pivô na mesma transação.
- [ ] O aluno que solicitou a monitoria (SLOT-19) e foi aprovado não precisa de convite: a solicitação já é o consentimento dele.
- [ ] Um aluno pode ser monitor de mais de uma turma.
- [ ] Remover um monitor cancela os horários futuros dele naquela turma (ligado quando a SLOT-11 existir).

### SLOT-11 · Monitor publica horários
`feature/` · **Prioridade:** Must · **Pontos:** 8

Como **monitor**, quero publicar os horários em que estou disponível, para que os alunos possam agendar.

**Cenários**
```gherkin
Cenário: Publicar um horário
  Dado que sou monitor de IMD0030 T01
  Quando publico um horário na terça das 14:00 às 15:00 na sala "CIVT A204" com 3 vagas
  Então o horário aparece entre os horários disponíveis de IMD0030

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

**Critérios de aceite**
- [ ] O horário tem turma, monitor, início, fim, local (uma sala do cadastro, um link online ou texto livre até a SLOT-21 existir) e vagas (≥ 1).
- [ ] O monitor pode publicar uma repetição semanal por N semanas (cria N horários).
- [ ] O monitor pode editar um horário sem agendamentos e cancelar qualquer horário (os agendamentos são cancelados e os alunos avisados, ver SLOT-18).
- [ ] Os horários de um mesmo monitor não se sobrepõem.

### SLOT-12 · Levantamento das salas do IMD
Tarefa · Sem código · **Prioridade:** Should · **Pontos:** 2

Percorrer os prédios do IMD (primeiro o CIVT, depois o nPITI) e listar toda sala que pode receber uma monitoria. O resultado alimenta a importação da SLOT-21.

**Critérios de aceite**
- [ ] Planilha com: prédio, andar, nome ou número da sala, tipo (`classroom | lab | study room | auditorium`) e capacidade.
- [ ] Exportada como CSV em `database/data/rooms.csv`, com o cabeçalho em inglês.
- [ ] Salas fechadas para alunos (escritórios, andar da incubadora) ficam de fora.

---

## Sprint 3 – O aluno encontra uma disciplina e agenda um atendimento (24/11 – 01/12)

### SLOT-13 · Buscar disciplinas e turmas
`feature/` · **Prioridade:** Must · **Pontos:** 5

Como **aluno**, quero buscar uma disciplina pelo código ou pelo nome, para encontrar rápido as turmas e os monitores dela.

**Cenários**
```gherkin
Cenário: Busca pelo código
  Quando busco "imd0030"
  Então vejo "IMD0030 – Linguagem de Programação I"

Cenário: Parte do nome sem acento
  Quando busco "programacao"
  Então vejo todas as disciplinas cujo nome contém "Programação"

Cenário: Abrir uma disciplina
  Quando abro IMD0030 nos resultados
  Então vejo as turmas dela no semestre atual
  E para cada turma: número, professores, horário e monitores

Cenário: Nenhum resultado
  Quando busco "xyz"
  Então vejo "Nenhuma disciplina encontrada" e uma dica para buscar pelo código ou pelo nome
```

**Critérios de aceite**
- [ ] Um único campo de busca encontra pelo código (prefixo) e pelo nome (contém), sem diferenciar maiúsculas nem acentos.
- [ ] A busca sem acentos funciona no SQLite (por exemplo, com uma coluna normalizada `search_name` preenchida ao salvar).
- [ ] Resultados paginados, disciplinas arquivadas escondidas.
- [ ] A busca fica disponível no menu lateral para todo usuário logado.

### SLOT-14 · Aluno vê os horários disponíveis
`feature/` · **Prioridade:** Must · **Pontos:** 3

Como **aluno**, quero ver os horários disponíveis de uma disciplina, para escolher um que funcione para mim.

**Cenários**
```gherkin
Cenário: Horários na página da disciplina
  Dado que IMD0030 T01 e T02 têm monitores com horários disponíveis
  Quando abro IMD0030 pela busca
  Então vejo os horários das duas turmas, em ordem de início

Cenário: Horários lotados
  Dado um horário com 2 vagas e 2 agendamentos
  Então ele aparece como "Lotado" e não pode ser agendado
```

**Critérios de aceite**
- [ ] A lista mostra o nome do monitor, a turma, data e hora, o local e as vagas restantes.
- [ ] Só aparecem horários futuros, não cancelados, de turmas do semestre atual.
- [ ] Filtro por período; lista paginada.

### SLOT-15 · Aluno agenda um horário
`feature/` · **Prioridade:** Must · **Pontos:** 5

Como **aluno**, quero agendar um horário e dizer com o que preciso de ajuda, para que o monitor possa se preparar.

**Cenários**
```gherkin
Cenário: Agendar um horário
  Dado um horário com vagas
  Quando agendo com o assunto "Ponteiros em C"
  Então o agendamento aparece em "Meus agendamentos"
  E as vagas restantes diminuem em uma

Cenário: Disputa pela última vaga
  Dado um horário com 1 vaga
  Quando dois alunos agendam ao mesmo tempo
  Então só um agendamento é confirmado
  E o outro aluno vê "Este horário está lotado"

Cenário: Agendar o próprio horário
  Dado que sou o monitor do horário
  Quando tento agendá-lo
  Então vejo "Você não pode agendar o seu próprio horário"

Cenário: Agendamento em conflito
  Dado que já tenho um agendamento que se sobrepõe a este horário
  Quando tento agendá-lo
  Então vejo "Você já tem um agendamento neste horário"
```

**Critérios de aceite**
- [ ] O agendamento guarda horário, aluno, assunto (opcional, até 500 caracteres) e status (`confirmed | cancelled | attended | no_show`).
- [ ] A checagem de vagas roda dentro de uma transação com lock na linha.
- [ ] Um aluno tem no máximo um agendamento ativo por horário.

### SLOT-16 · Cancelar agendamento
`feature/` · **Prioridade:** Must · **Pontos:** 2

Como **aluno**, quero cancelar meu agendamento, para que a vaga fique livre para outra pessoa.

**Cenários**
```gherkin
Cenário: Cancelar a tempo
  Dado que meu agendamento começa daqui a mais de 2 horas
  Quando cancelo
  Então ele aparece como "Cancelado" e a vaga é liberada

Cenário: Cancelar tarde demais
  Dado que meu agendamento começa daqui a menos de 2 horas
  Quando tento cancelar
  Então vejo "Agendamentos só podem ser cancelados até 2 horas antes"
```

**Critérios de aceite**
- [ ] O prazo de cancelamento vem da configuração (padrão de 2 horas).
- [ ] Agendamentos cancelados continuam no histórico.

### SLOT-17 · Lista de atendimentos do monitor
`feature/` · **Prioridade:** Must · **Pontos:** 3

Como **monitor**, quero ver meus próximos atendimentos com quem agendou e o assunto, para poder me preparar.

**Critérios de aceite**
- [ ] Próximos atendimentos agrupados por dia, mostrando cada aluno e o assunto.
- [ ] Cada atendimento leva ao perfil do aluno e, quando a SLOT-20 existir, a uma conversa com ele.

### SLOT-18 · Notificações por e-mail de agendamentos
`feature/` · **Prioridade:** Should · **Pontos:** 3

Como **monitor** ou **aluno**, quero receber um e-mail quando um agendamento é feito ou cancelado, para não perder nenhuma mudança.

**Critérios de aceite**
- [ ] O monitor recebe um e-mail quando um aluno agenda ou cancela.
- [ ] O aluno recebe um e-mail quando o monitor cancela o horário.
- [ ] Os e-mails vão para a fila (não são enviados durante a requisição).
- [ ] Os testes usam `Notification::fake()` para conferir quem foi notificado.

### SLOT-19 · Aluno solicita ser monitor
`feature/` · **Prioridade:** Should · **Pontos:** 5

Como **aluno**, quero solicitar ser monitor de uma turma, para que o professor dela avalie e me aprove.

**Cenários**
```gherkin
Cenário: Aluno envia uma solicitação
  Dado que sou aluno e não sou monitor de IMD0030 T01
  Quando solicito ser monitor de IMD0030 T01 com uma mensagem curta
  Então a solicitação aparece como "Pendente" para mim
  E os professores de IMD0030 T01 a veem na página da turma

Cenário: O professor aprova
  Dado que existe uma solicitação pendente do João para IMD0030 T01
  Quando o professor aprova
  Então o João vira monitor de IMD0030 T01
  E a solicitação aparece como "Aprovada"

Cenário: O professor recusa
  Quando o professor recusa informando um motivo
  Então o João vê "Recusada" e o motivo

Cenário: Solicitação repetida
  Dado que já tenho uma solicitação pendente para IMD0030 T01
  Quando tento solicitar de novo
  Então vejo "Você já tem uma solicitação pendente para esta turma"
```

**Critérios de aceite**
- [ ] Botão de solicitar no card da turma, na página da disciplina (SLOT-13).
- [ ] A solicitação tem status `pending | approved | rejected`, mensagem opcional e motivo da recusa.
- [ ] Só uma solicitação pendente por aluno por turma. Quem já é monitor da turma não pode solicitar.
- [ ] A aprovação cria a linha em `class_monitor` na mesma transação.

---

## Sprint 4 – Chat, salas e entrega (01/12 – 08/12)

### SLOT-20 · Mensagens diretas
`feature/` · **Prioridade:** Should · **Pontos:** 5

Como **aluno**, quero mandar mensagem para um monitor ou professor dentro do Slot, para tirar uma dúvida sem precisar passar meu WhatsApp.

**Cenários**
```gherkin
Cenário: Começar uma conversa pelo perfil
  Dado que estou no perfil do monitor João
  Quando clico em "Enviar mensagem"
  Então abre uma conversa com o João
  E consigo mandar uma mensagem para ele

Cenário: Aluno não puxa conversa com aluno
  Dado que o Pedro é um aluno que não é monitor
  Quando tento começar uma conversa com o Pedro
  Então a conversa não é criada

Cenário: Privacidade
  Dado uma conversa entre a Maria e o João
  Quando o Pedro abre a URL dela
  Então ele recebe um erro 403
```

**Critérios de aceite**
- [ ] Só conversas entre duas pessoas (sem grupos). Começar uma conversa com quem você já conversa reabre a conversa existente.
- [ ] Qualquer pessoa pode começar uma conversa com um professor ou monitor. Um aluno não pode começar uma conversa com um aluno comum, mas pode responder em uma conversa que já existe.
- [ ] Só os dois participantes leem ou escrevem na conversa.
- [ ] Mensagens são texto puro, até 2000 caracteres, escapadas na renderização.
- [ ] A página da conversa mostra as mensagens em ordem e atualiza ao enviar.

### SLOT-29 · Caixa de entrada e mensagens não lidas
`feature/` · **Prioridade:** Should · **Pontos:** 5

Como **qualquer usuário**, quero ver todas as minhas conversas e quais têm mensagens novas, para não perder nenhuma resposta.

**Cenários**
```gherkin
Cenário: Receber uma mensagem
  Dado que o João me mandou uma mensagem
  Quando abro o app
  Então vejo um indicador de não lidas em "Mensagens"

Cenário: Ler zera o indicador
  Dado que tenho 2 mensagens não lidas do João
  Quando abro nossa conversa
  Então o contador de não lidas dela vai para 0

Cenário: Mensagem nova com a conversa aberta
  Dado que estou com a conversa com o João aberta
  Quando o João manda uma mensagem
  Então ela aparece em poucos segundos, sem recarregar a página
```

**Critérios de aceite**
- [ ] A página "Mensagens" lista as conversas pela última mensagem, com o nome da outra pessoa, uma prévia e o número de não lidas.
- [ ] O item "Mensagens" do menu lateral mostra o total de não lidas.
- [ ] Abrir uma conversa marca as mensagens dela como lidas.
- [ ] Mensagens novas aparecem sem recarregar a página (polling a cada poucos segundos basta; o Laravel Reverb é um objetivo extra).
- [ ] Funciona em 375px: no mobile, a lista e a conversa são telas separadas.

### SLOT-21 · Cadastro de salas
`feature/` · **Prioridade:** Should · **Pontos:** 8

Como **admin**, quero cadastrar as salas do IMD, uma a uma ou importando o CSV do levantamento, para que horários e reservas apontem para lugares reais.

**Cenários**
```gherkin
Cenário: Importar o levantamento
  Dado o CSV da SLOT-12
  Quando eu o importo
  Então cada linha válida vira uma sala
  E vejo um resumo: criadas, atualizadas e ignoradas com o motivo

Cenário: Reimportar atualiza
  Dado que "CIVT A204" existe com capacidade 30
  Quando importo um CSV em que "CIVT A204" tem capacidade 40
  Então a sala é atualizada, e não duplicada

Cenário: Desativar uma sala
  Quando desativo "CIVT A204"
  Então ela deixa de ser oferecida para horários e reservas
```

**Critérios de aceite**
- [ ] A sala tem prédio, andar, nome ou número, tipo, capacidade e um indicador de ativa. Prédio + nome é único.
- [ ] O admin cria, edita e desativa salas, e filtra a lista por prédio e tipo.
- [ ] A importação do CSV valida todas as linhas e nunca importa pela metade sem avisar.
- [ ] O formulário de turma (SLOT-8) e o de horário (SLOT-11) oferecem as salas ativas.

### SLOT-22 · Reservar sala
`feature/` · **Prioridade:** Should · **Pontos:** 5

Como **monitor** ou **professor**, quero reservar uma sala para um horário, para ter onde fazer o atendimento.

**Cenários**
```gherkin
Cenário: Reservar uma sala livre
  Dado que "CIVT A204" está livre na terça das 14:00 às 15:00
  Quando eu a reservo para esse horário
  Então a reserva é confirmada

Cenário: Conflito
  Dado que "CIVT A204" está reservada na terça das 14:00 às 15:00
  Quando tento reservá-la na terça das 14:30 às 15:30
  Então vejo "A CIVT A204 já está reservada neste horário"

Cenário: Encontrar uma sala livre
  Quando procuro salas livres na terça das 14:00 às 15:00 para 10 pessoas no CIVT
  Então vejo só salas ativas do CIVT com capacidade ≥ 10 e sem reserva nesse horário

Cenário: Usar a sala em um horário de monitoria
  Dado que tenho uma reserva da CIVT A204 na terça das 14:00 às 15:00
  Quando publico um horário nesse período
  Então posso escolher essa reserva como local
```

**Critérios de aceite**
- [ ] Não há reservas sobrepostas para a mesma sala (checado em uma transação).
- [ ] A página da sala mostra as reservas da semana em uma lista ou calendário simples.
- [ ] Alunos que não são monitores não reservam salas.
- [ ] Cancelar um horário de monitoria não cancela a reserva da sala (quem reservou é que cancela).

### SLOT-23 · Buscar professores e monitores
`feature/` · **Prioridade:** Should · **Pontos:** 5

Como **aluno**, quero buscar professores e monitores pelo nome, para descobrir quem dá aula ou monitoria de quê e como falar com eles.

**Cenários**
```gherkin
Cenário: Buscar um monitor
  Dado que o João é monitor de IMD0030 T01
  Quando busco "joao" em Pessoas
  Então vejo o João marcado como "Monitor" de "IMD0030"

Cenário: Perfil do monitor
  Quando abro o perfil do João
  Então vejo as turmas em que ele é monitor e os próximos horários disponíveis dele
  E consigo agendar um horário por ali

Cenário: Perfil do professor
  Quando abro o perfil da professora Ana
  Então vejo as turmas em que ela dá aula neste semestre

Cenário: Alunos que não são monitores
  Quando busco um aluno que não é monitor
  Então ele não aparece nos resultados
```

**Critérios de aceite**
- [ ] A busca de pessoas encontra pelo nome, sem diferenciar maiúsculas nem acentos, com filtro por `professor | monitor`.
- [ ] Só professores e monitores do semestre atual aparecem na busca. Alunos comuns não são listados (privacidade).
- [ ] O perfil mostra nome, papel, turmas e, para monitores, os próximos horários disponíveis. O e-mail não aparece.
- [ ] O perfil já tem o lugar de um botão "Enviar mensagem", que a SLOT-20 ativa.

### SLOT-24 · Revisão de mobile e acessibilidade
`enhancement/` · **Prioridade:** Must · **Pontos:** 5

**Critérios de aceite**
- [ ] Todos os fluxos testados em um celular de verdade (Android e iOS), corrigindo o que as checagens de cada PR deixaram passar.
- [ ] Todo formulário mostra os erros de validação do servidor ao lado do campo.
- [ ] A navegação por teclado funciona nos fluxos de busca, agendamento e chat.
- [ ] Todos os textos da interface revisados para ficarem consistentes.

### SLOT-25 · Dados de demonstração e deploy
`chore/` · **Prioridade:** Must · **Pontos:** 5

**Critérios de aceite**
- [ ] O seeder cria uma demonstração realista: disciplinas e turmas do BTI, professores, monitores, horários, agendamentos, as salas levantadas e algumas conversas.
- [ ] `develop` mergeada na `main` e publicada.
- [ ] Tabela de funcionalidades do README atualizada com o que foi entregue.

### SLOT-26 · Apresentação final
Tarefa · **Prioridade:** Must · **Pontos:** 3

**Critérios de aceite**
- [ ] Roteiro da demonstração com um fluxo completo por papel.
- [ ] Slides com o histórico das sprints (objetivo, o que foi entregue, o que foi cortado, velocidade).

### SLOT-27 · Dashboard por papel
`enhancement/` · **Prioridade:** Could · **Pontos:** 5

Como **qualquer usuário**, quero que o dashboard mostre o que importa para o meu papel, para achar rápido minha próxima ação.

**Critérios de aceite**
- [ ] Aluno: próximos agendamentos, mensagens não lidas e o campo de busca de disciplinas.
- [ ] Monitor: próximos atendimentos, por turma.
- [ ] Professor: suas turmas e a quantidade de solicitações de monitoria pendentes.
- [ ] Admin: totais de usuários, disciplinas, turmas e salas.
- [ ] Os estados vazios têm uma mensagem curta e um link para a próxima ação.

### SLOT-28 · Controle de presença
`feature/` · **Prioridade:** Could · **Pontos:** 3

Como **monitor**, quero marcar quem compareceu a cada atendimento, para que o professor acompanhe o trabalho da monitoria.

**Critérios de aceite**
- [ ] Depois que o atendimento termina, o monitor marca cada agendamento como `attended` ou `no_show`.
- [ ] O professor vê o total de presenças por monitor na página da turma (SLOT-9).

---

## Fora do escopo

- Integração com o SIGAA, login da UFRN ou qualquer sistema da universidade (sem importar disciplinas, turmas ou matrículas).
- Modelar projetos de monitoria da PROGRAD, bolsas ou editais de seleção.
- Chats em grupo, anexos e notificações push.
- Avaliação dos atendimentos e fila de espera para horários lotados.
