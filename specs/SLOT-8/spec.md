# SLOT-8 · Turmas

Card: https://slotgestao.atlassian.net/browse/SLOT-8
Tipo: História · Branch: `feature/SLOT-8-turmas` · Base: `develop`
Protótipo: https://www.figma.com/design/nfTNXwoHBdJ2If6mGditTw?node-id=10-2653
Aprovado por: Guilherme Higino

## Objetivo

O admin cadastra as turmas de cada disciplina no semestre, com professores e horário, para que os professores gerenciem seus monitores (SLOT-9 e SLOT-10) e as buscas e horários apontem para a turma certa. Quem usa esta tela é só o admin.

## Regras de negócio

- R1: só admin acessa as turmas; professor e aluno recebem 403.
- R2: a turma tem disciplina, semestre, número, um ou mais professores e código de horário. Todos obrigatórios.
- R3: a disciplina é escolhida entre as ativas. Uma turma que já existe com disciplina arquivada mantém a disciplina na edição.
- R4: o semestre segue `^\d{4}\.[12]$`; senão, "O semestre deve estar no formato ano.1 ou ano.2". O campo vem preenchido com o semestre atual e aceita outro, passado ou futuro.
- R5: o semestre atual vem de `SLOT_CURRENT_SEMESTER` (`2026.2` no `.env.example`).
- R6: antes de validar, o número perde os espaços das pontas e vai para maiúsculas ("t01" vira "T01"). Segue `^T\d{2}$`; senão, "O número da turma deve ser T seguido de 2 dígitos".
- R7: disciplina + semestre + número é único; senão, "Esta turma já existe neste semestre". Na edição, a própria turma não conta como repetida.
- R8: o código de horário é texto livre de até 50 caracteres, sem validação de formato, só para exibição.
- R9: só usuários com papel professor aparecem como opção e são aceitos como professores; senão, "Selecione ao menos um professor" (vazio) ou erro de validação (id que não é professor).
- R10: o admin edita qualquer turma com as regras R3 a R9. Não existe excluir.
- R11: a lista é paginada de 15 em 15, ordenada pelo código da disciplina e depois pelo número. Filtros: disciplina (padrão "Todas as disciplinas") e semestre (padrão o atual; opções: o atual e os que têm turmas).
- R12: existe um jeito reutilizável de filtrar turmas do semestre atual, para os cards SLOT-13 e SLOT-14.
- R13: o seeder cria mais 2 professores e 4 turmas em 2026.2, uma delas com dois professores.

## Cenários

```gherkin
Cenário: Admin cria uma turma
  Dado que estou logado como admin
  E que a disciplina IMD0030 existe
  Quando crio a turma "T01" do semestre "2026.2" com a professora Ana e o horário "35M12"
  Então a turma aparece na lista com a professora Ana
  E vejo "Turma salva com sucesso!"

Cenário: Turma repetida
  Dado que IMD0030 T01 existe em 2026.2
  Quando crio IMD0030 T01 em 2026.2 de novo
  Então vejo "Esta turma já existe neste semestre"

Cenário: Só professores dão aula
  Quando tento colocar um aluno como professor de uma turma
  Então o aluno não aparece como opção
  E o envio com o id do aluno é recusado

Cenário: O número é normalizado
  Quando crio uma turma com o número "t01 "
  Então ela é salva como "T01"

Cenário: Semestre inválido
  Quando crio uma turma com o semestre "2026.3"
  Então vejo "O semestre deve estar no formato ano.1 ou ano.2"

Cenário: Disciplina arquivada não recebe turma nova
  Dado que a disciplina IMD0030 está arquivada
  Então ela não aparece como opção em "Nova turma"
  E o envio com IMD0030 é recusado

Cenário: Admin edita uma turma
  Dado que IMD0030 T01 existe com a professora Ana
  Quando adiciono o professor Carlos e salvo
  Então a lista mostra "Ana Costa e Carlos Alves"

Cenário: Filtrar por disciplina e semestre
  Dado IMD0030 T01 em 2026.2 e IMD0012 T01 em 2026.1
  Quando abro a lista
  Então vejo só IMD0030 T01
  Quando filtro o semestre 2026.1
  Então vejo só IMD0012 T01

Cenário: Quem não é admin não acessa as turmas
  Dado que estou logado como professor
  Quando abro /admin/turmas
  Então recebo um erro 403
```

## Tela (protótipo do Figma)

- **Lista:** breadcrumb "Administração / Turmas", título "Turmas", subtítulo "Gerencie as turmas e seus professores no semestre.", botão "Nova turma" e os filtros Disciplina e Semestre. Colunas Disciplina ("IMD0030 · Linguagem de Programação I"), Turma, Semestre, Professores ("Ana Costa e Carlos Alves"), Horário e Ações ("Editar turma"). Rodapé "Exibindo X–Y de Z turmas" com Anterior e Próximo.
- **Mobile (375px):** cada turma vira um card com os mesmos dados e o botão "Editar turma".
- **Criar e editar:** modal no meio da tela, sem sair da lista. "Nova turma" ("Vincule a disciplina aos professores deste semestre.") e "Editar turma" ("Atualize os dados da turma selecionada."). Campos Disciplina ("Selecione a disciplina"), Semestre (dica "Formato: ano.1 ou ano.2"), Número da turma ("Ex.: T01"), Professores ("Selecione um ou mais professores") e Código de horário ("Ex.: 35M12", dica "Código exibido aos alunos."). Botões "Cancelar" e "Salvar turma". Erros ao lado do campo.
- **Selecionar professores (modal):** "Selecionar professores", "Somente professores aparecem nesta lista.", "Selecione uma ou mais pessoas para esta turma.", busca por nome, lista com o estado "selecionado(a)", botões "Aplicar seleção" e "Cancelar".
- **Mensagem:** "Turma salva com sucesso!" (criar e editar).
- **Estados vazios:** sem turmas no filtro, "Nenhuma turma encontrada" e "Tente outro semestre ou disciplina.". Sem nenhuma turma cadastrada, "Nenhuma turma cadastrada" e "Comece cadastrando a primeira turma do semestre." com o botão "Nova turma".
- **Menu:** item "Turmas" no grupo "Acadêmico", abaixo de "Disciplinas".

## Critérios de aceite

- [ ] A turma tem disciplina, semestre (`YYYY.1` ou `YYYY.2`), número (`T01`), um ou mais professores e código de horário (texto livre, só para exibição).
- [ ] Disciplina + semestre + número é único.
- [ ] O semestre atual vem da configuração (`SLOT_CURRENT_SEMESTER`), com um filtro reutilizável de turmas do semestre atual.
- [ ] As telas seguem o protótipo (com as mudanças de Decisões) e funcionam com 375px de largura.

## Fora do escopo

- Sala da turma: a SLOT-21 cria as salas e adiciona o campo ao formulário.
- "Minhas turmas" do professor (SLOT-9) e convite de monitores (SLOT-10).
- Aplicar o filtro de semestre atual em buscas (SLOT-13) e horários (SLOT-14).
- Excluir uma turma.
- O que acontece com as turmas de um professor que muda de papel: ele continua ligado a elas.

## Decisões

Todas por Guilherme, aceitando as recomendações da entrevista:

- Cenário 1 termina na lista do admin; "Minhas turmas" fica na SLOT-9.
- Criar e editar em modal, como em disciplinas (o protótipo mobile usa página). Sem excluir.
- Disciplina arquivada fora das opções de turma nova (R3).
- Formatos de semestre, número e horário como em R4, R6 e R8.
- Professores: ao menos um, só papel professor, modal com busca por nome.
- Lista, filtros e seeder como em R11 e R13.

## Suposições

- Textos que o protótipo não mostra (subtítulo de "Editar turma", estados vazios, mensagens de erro, "Turma salva com sucesso!") seguem o padrão do catálogo de disciplinas.
- O protótipo não mostra sala no formulário; isso bate com deixar a sala para a SLOT-21.
- O menu do protótipo ("Espaço administrativo", rodapé "Semestre 2026.2") não é adotado, como no SLOT-7.
- Professores aparecem na lista em ordem alfabética, juntos por vírgula e "e".
- A busca do modal de professores filtra a lista já carregada na tela, sem ir ao servidor.
