# SLOT-7 · Catálogo de disciplinas

Card: https://slotgestao.atlassian.net/browse/SLOT-7
Tipo: História · Branch: `feature/SLOT-7-catalogo-disciplinas` · Base: `develop`
Protótipo: https://www.figma.com/design/nfTNXwoHBdJ2If6mGditTw?node-id=2-2
Aprovado por:

## Objetivo

O admin mantém o catálogo de disciplinas da UFRN (código e nome) para que turmas (SLOT-8), horários (SLOT-11) e buscas (SLOT-13) apontem sempre para a disciplina certa. Quem usa é só o admin.

## Regras de negócio

- R1: só admin acessa o catálogo; professor e aluno recebem 403.
- R2: antes de validar, o código perde os espaços das pontas e vai para maiúsculas ("imd0030 " vira "IMD0030").
- R3: o código segue `^[A-Z]{3}\d{4}$`; senão, "O código deve ter 3 letras seguidas de 4 dígitos".
- R4: o código é único entre todas as disciplinas, arquivadas incluídas; senão, "Este código já está em uso". Na edição, a própria disciplina não conta como repetida.
- R5: o nome é obrigatório, tem até 255 caracteres e pode repetir entre disciplinas.
- R6: o admin edita código e nome de qualquer disciplina, arquivada ou não, com as regras R2 a R5.
- R7: arquivar pede confirmação. A disciplina arquivada continua na lista do admin com o status "Arquivada", mantém o histórico e deixa de aparecer em buscas e de aceitar novos horários.
- R8: não existe reativar neste card.
- R9: a lista é paginada de 15 em 15, ordenada por código. A busca filtra por código ou nome (contém, sem diferenciar maiúsculas).
- R10: o seeder cria as 14 disciplinas dos níveis 1 a 3 do BTI (lista no fim da spec).

## Cenários

```gherkin
Cenário: Admin cria uma disciplina
  Dado que estou logado como admin
  Quando crio a disciplina "IMD0030" com o nome "Linguagem de Programação I"
  Então ela aparece no catálogo de disciplinas
  E vejo "Disciplina salva com sucesso!"

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

Cenário: Admin edita uma disciplina
  Dado que existe a disciplina "IMD0030"
  Quando mudo o nome para "Linguagem de Programação 1"
  Então a lista mostra o novo nome

Cenário: Salvar sem mudar o código
  Dado que existe a disciplina "IMD0030"
  Quando edito só o nome e salvo
  Então não vejo "Este código já está em uso"

Cenário: Admin arquiva uma disciplina
  Dado que existe a disciplina ativa "IMD0030"
  Quando clico em "Arquivar" e confirmo
  Então ela aparece como "Arquivada" na lista
  E vejo "Disciplina arquivada com sucesso!"

Cenário: Cancelar o arquivamento
  Quando clico em "Arquivar" e depois em "Cancelar"
  Então a disciplina continua ativa

Cenário: Buscar por código ou nome
  Dado as disciplinas "IMD0030" e "IMD0012"
  Quando busco "0030"
  Então vejo só "IMD0030"

Cenário: Estados vazios
  Dado que não há disciplinas
  Então vejo "Nenhuma disciplina cadastrada"
  Quando há disciplinas e busco "IMD9999"
  Então vejo "Nenhuma disciplina encontrada"

Cenário: Quem não é admin não acessa o catálogo
  Dado que estou logado como aluno
  Quando abro /admin/courses
  Então recebo um erro 403
```

## Tela (protótipo do Figma)

- **Lista:** breadcrumb "Administração / Disciplinas", título "Disciplinas", subtítulo "Gerencie as disciplinas disponíveis para monitoria.", botão "+ Nova disciplina" e busca "Buscar por código ou nome". Colunas Código, Nome, Status (Ativa ou Arquivada) e Ações (Editar e Arquivar; na arquivada, o segundo botão aparece desabilitado como "Arquivada"). Rodapé "Exibindo X–Y de Z disciplinas" com Anterior e Próximo.
- **Mobile (375px):** cada disciplina vira um card com código e selo de status, nome e os botões Editar e Arquivar.
- **Criar e editar:** página própria, não modal. "Nova disciplina" ("Cadastre uma disciplina no catálogo acadêmico.") e "Editar disciplina" ("Atualize os dados da disciplina selecionada."). Campos Código (placeholder "Ex.: IMD0030", dica "Identificador oficial da disciplina no IMD.") e Nome (placeholder "Ex.: Linguagem de Programação I"). Botões "Cancelar" e "Salvar disciplina". Erros ao lado do campo.
- **Confirmar arquivamento (modal):** "A disciplina deixará de aparecer nas buscas e não aceitará novos horários. O histórico será mantido.", com "IMD0030 — Linguagem de Programação I" e "Não será possível criar novos agendamentos para esta disciplina." Botões "Arquivar disciplina" e "Cancelar".
- **Mensagens:** "Disciplina salva com sucesso!" (criar e editar) e "Disciplina arquivada com sucesso!".
- **Estados vazios:** sem disciplinas, "Nenhuma disciplina cadastrada" e "Comece cadastrando a primeira disciplina do catálogo." com o botão "+ Nova disciplina". Busca sem resultado, "Nenhuma disciplina encontrada" e "Tente buscar por outro código ou nome."
- **Menu:** novo item "Disciplinas" no grupo de administração, abaixo de "Usuários".

## Critérios de aceite

- [ ] A disciplina tem código (único, `^[A-Z]{3}\d{4}$`, salvo em maiúsculas e sem espaços) e nome.
- [ ] O admin cria, edita e arquiva disciplinas. Disciplinas arquivadas não aparecem nas buscas nem aceitam novos horários, mas mantêm o histórico.
- [ ] Seeder com as principais disciplinas do BTI, para o app não ficar vazio no desenvolvimento.
- [ ] As telas seguem o protótipo e funcionam com 375px de largura.

## Fora do escopo

- Reativar uma disciplina arquivada.
- Esconder arquivadas nas buscas (SLOT-13) e bloquear novos horários nelas (SLOT-11): esses cards precisam respeitar o estado "arquivada" que este card cria.
- Busca sem acento (SLOT-13) e o item "Turmas" do menu (SLOT-8).
- Refazer o visual do menu ("Visão geral", "Espaço administrativo", rodapé "Semestre 2026.2") que aparece no protótipo.
- Excluir uma disciplina de vez.

## Decisões

- Protótipo do Figma é a referência de tela → sim (Renan)
- Editar o código de uma disciplina → pode, com as mesmas validações (Renan)
- Código de disciplina arquivada → continua reservado, não dá para reutilizar (Renan)
- Reativar → fica fora deste card (Renan)
- Lista do admin → mostra arquivadas com selo, 15 por página, busca por código e nome (Renan)
- Nome → obrigatório, até 255 caracteres, pode repetir (Renan)
- Seeder → disciplinas reais do início do BTI (Renan)

## Suposições

- O protótipo tem textos diferentes entre desktop e mobile em três lugares: estado vazio, dica do campo Código e aviso de arquivar. Adotei a versão desktop, e no aviso de arquivar a que bate com o texto do card.
- Disciplina arquivada pode ser editada, porque o protótipo mostra "Editar" ativo na linha dela.
- O protótipo usa "Exibindo X–Y de Z disciplinas"; o componente de paginação da SLOT-6 mostra "Página X de Y". Adotei o protótipo, adaptando o componente compartilhado.
- A ordem das linhas no protótipo não segue o código (parece dado de exemplo); ordenei por código.
- Os códigos e nomes de exemplo do protótipo (IMD0031, IMD0015, IMD0027) não batem com a grade do SIGAA. O seeder usa a grade oficial.
- O seeder traz as 14 disciplinas da captura da grade do BTI (níveis 1 a 3), e não as 8 a 12 combinadas, porque os dados já estavam em mãos. Sem disciplina arquivada no seeder.

## Anexo: disciplinas do seeder

| Código  | Nome                                                   |
| ------- | ------------------------------------------------------ |
| IMD0012 | Introdução às Técnicas de Programação                  |
| IMD0017 | Práticas de Leitura e Escrita em Português I           |
| IMD0018 | Práticas de Leitura em Inglês                          |
| IMD0019 | Resolução de Problemas Matemáticos para TI             |
| IMD0020 | Tecnologia da Informação e Sociedade                   |
| IMD0024 | Cálculo Diferencial e Integral I                       |
| IMD0027 | Práticas de Leitura e Escrita em Português II          |
| IMD0028 | Fundamentos Matemáticos da Computação I                |
| IMD0029 | Estrutura de Dados Básicas I                           |
| IMD0030 | Linguagem de Programação I                             |
| IMD0033 | Probabilidade                                          |
| IMD0034 | Vetores e Geometria Analítica                          |
| IMD0038 | Fundamentos Matemáticos da Computação II               |
| IMD0041 | Introdução à Organização e Arquitetura de Computadores |
