# SLOT-13 · Buscar disciplinas e turmas

Card: https://slotgestao.atlassian.net/browse/SLOT-13
Tipo: História · Branch: `feature/SLOT-13-buscar-disciplinas-turmas` · Base: `enhancement/SLOT-35-menu-prototipo`
Aprovado por: Guilherme Higino

## Objetivo

O aluno procura ajuda pela disciplina, não pela turma: ganha uma busca por código ou nome e uma página da disciplina com descrição, turmas do semestre e professores anteriores. Porta de entrada da SLOT-14 e da SLOT-19.

## Regras de negócio

- R1: Um único campo de busca encontra a disciplina pelo código (prefixo) ou pelo nome (contém), sem diferenciar maiúsculas nem acentos.
- R2: A busca sem acento funciona no SQLite por meio de uma coluna normalizada `search_name` (nome em minúsculas e sem acento), preenchida sempre que a disciplina é salva. As disciplinas que já existem recebem o valor na migration.
- R3: Termo vazio (ou só espaços) lista todas as disciplinas ativas.
- R4: Os resultados são paginados, ordenados por código, e as disciplinas arquivadas nunca aparecem.
- R5: Cada resultado aparece como `<CÓDIGO> – <Nome>` e leva à página da disciplina.
- R6: A página da disciplina fica em `/disciplinas/<CÓDIGO>`. Disciplina arquivada ou código inexistente devolve 404 para todos, admin incluído.
- R7: A busca e a página são acessíveis a todo usuário logado e verificado, de qualquer papel.
- R8: A disciplina ganha uma descrição opcional de até 500 caracteres, em texto puro. O admin a escreve no modal de criar e editar do catálogo (SLOT-7), e a página da disciplina a exibe mantendo as quebras de linha. Descrição vazia é salva como nula, e a seção não aparece.
- R9: A página lista as turmas do semestre atual (`slot.current_semester`), em ordem de número. Cada turma mostra o número, os professores, o código de horário e os monitores, só com os nomes e em ordem alfabética.
- R10: A página lista os professores das turmas de semestres anteriores ao atual, sem nomes repetidos e em ordem alfabética. Turmas de semestres futuros não contam. Um professor do semestre atual pode aparecer nas duas seções.
- R11: As turmas e os professores anteriores na página da disciplina não são paginados.
- R12: O espaço de cada papel no menu lateral (SLOT-35) ganha o item "Buscar disciplinas", logo depois de "Visão geral"; no professor, vem primeiro.
- R13: Nenhum e-mail aparece na busca nem na página da disciplina.
- R14: Carga horária opcional, em horas, importada do SIGAA pelo seeder. A página a mostra ao lado do nome ("· 60h"); o admin não a edita.

## Cenários

```gherkin
Cenário: Busca pelo código
  Quando busco "imd0030"
  Então vejo "IMD0030 – Linguagem de Programação I"

Cenário: Parte do nome sem acento
  Quando busco "programacao"
  Então vejo todas as disciplinas cujo nome contém "Programação"

Cenário: Disciplina arquivada não aparece
  Dado que IMD0030 está arquivada
  Quando busco "imd0030"
  Então não vejo IMD0030 nos resultados

Cenário: Abrir uma disciplina
  Quando abro IMD0030 nos resultados
  Então vejo as turmas dela no semestre atual
  E para cada turma: número, professores, horário e monitores

Cenário: Descrição da disciplina
  Dado que o admin escreveu uma descrição para IMD0030
  Quando abro IMD0030
  Então vejo a descrição da disciplina

Cenário: Admin escreve a descrição
  Dado que estou logado como admin
  Quando edito IMD0030 no catálogo e informo uma descrição
  Então a descrição é salva

Cenário: Descrição longa demais
  Quando salvo uma disciplina com uma descrição de 501 caracteres
  Então vejo "A descrição deve ter no máximo 500 caracteres."

Cenário: Professores que já deram a disciplina
  Dado que IMD0030 teve turmas em semestres anteriores
  Quando abro IMD0030
  Então vejo os professores dessas turmas, sem nomes repetidos

Cenário: Página de disciplina arquivada
  Dado que IMD0030 está arquivada
  Quando acesso /disciplinas/IMD0030
  Então recebo 404

Cenário: Nenhum resultado
  Quando busco "xyz"
  Então vejo "Nenhuma disciplina encontrada" e uma dica para buscar pelo código ou pelo nome
```

## Critérios de aceite

- [ ] Um único campo de busca encontra pelo código (prefixo) e pelo nome (contém), sem diferenciar maiúsculas nem acentos.
- [ ] A busca sem acentos funciona no SQLite com a coluna normalizada `search_name`, preenchida ao salvar.
- [ ] Resultados paginados, disciplinas arquivadas escondidas.
- [ ] A busca fica disponível no menu lateral para todo usuário logado.
- [ ] A disciplina ganha uma descrição opcional (até 500 caracteres), que o admin escreve no modal de criar e editar do catálogo e o aluno lê na página da disciplina.
- [ ] A página da disciplina lista os professores que já deram a disciplina em semestres anteriores.
- [ ] Busca e página da disciplina funcionam com 375px de largura.

## Fora do escopo

- Horários disponíveis dos monitores na página da disciplina (SLOT-14).
- Botão para solicitar ser monitor no card da turma (SLOT-19).
- Busca de pessoas e perfis; aqui os nomes não são links (SLOT-23).
- Markdown ou formatação rica na descrição.
- O visual do menu lateral e do cabeçalho mobile (SLOT-35).

## Decisões

Todas por Guilherme Higino:

- URL → pelo código (R6)
- Busca vazia → lista todas as ativas (R3)
- Arquivada por link direto → 404 para todos (R6)
- Professores anteriores → só semestres anteriores, sem repetição (R10)
- Paginação da página → nenhuma: listas pequenas (R11)
- Menu → "Buscar disciplinas" para todos (R12)
- Pessoas na turma → só o nome (R9, R13)
- Descrição → texto puro (R8)
- Carga horária → do SIGAA, só leitura (R14)

## Suposições

- A busca segue o catálogo do admin: formulário GET, sem buscar enquanto digita.
- 15 resultados por página.
- "IMD 0030" (com espaço) não encontra IMD0030.
- Turma sem monitores: "Sem monitores". Disciplina sem turmas no semestre: "Nenhuma turma neste semestre". Sem professores anteriores, a seção some.
- "Semestre anterior" é comparado como texto `YYYY.N` (`2026.1` < `2026.2`).
