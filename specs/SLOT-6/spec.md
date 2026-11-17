# SLOT-6 · Papéis de usuário

Card: https://slotgestao.atlassian.net/browse/SLOT-6
Tipo: História · Branch: `feature/SLOT-6-papeis-de-usuario` · Base: `develop`
Aprovado por:

## Objetivo

Hoje todo usuário do Slot é igual. O admin precisa que cada pessoa tenha um papel (admin, professor ou aluno) para que cada uma só veja e faça o que cabe a ela. É a base de permissão de todos os cards seguintes.

## Regras de negócio

- R1: Todo usuário tem exatamente um papel: admin, professor ou aluno.
- R2: Quem se cadastra pela página de cadastro entra como aluno. Ninguém escolhe o próprio papel.
- R3: Só admin acessa as rotas de admin (`/admin/...`). Professor e aluno recebem 403; quem não está logado vai para o login.
- R4: Só admin muda o papel de alguém, pela página de usuários do admin.
- R5: O admin não consegue tirar o próprio papel de admin.
- R6: A mudança de papel vale na próxima requisição da pessoa, sem precisar sair e entrar de novo.
- R7: O menu lateral mostra os itens do papel de quem está logado.

## Cenários

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

Cenário: Admin não tira o próprio papel de admin
  Dado que estou logado como admin
  Quando tento mudar o meu papel para "aluno"
  Então vejo um erro e continuo admin

Cenário: Admin busca um usuário
  Dado que estou logado como admin
  Quando busco por parte do nome ou do e-mail da Maria
  Então a lista mostra a Maria e esconde quem não combina com a busca
```

## Critérios de aceite

- [ ] Coluna `role` em `users` com um enum PHP (`admin`, `professor`, `student`), padrão `student`.
- [ ] Middleware ou gate restringe as rotas de admin e devolve 403 para os demais.
- [ ] Página de usuários do admin com busca por nome ou e-mail e um seletor de papel.
- [ ] O admin não consegue tirar o próprio papel de admin (evita ficar sem acesso).
- [ ] O `DatabaseSeeder` cria `admin@slot.test`, `professor@slot.test` e `student@slot.test`.
- [ ] Uma prop compartilhada do Inertia expõe o papel do usuário logado, e o menu lateral mostra os itens de cada papel.

## Fora do escopo

- Monitor não é papel: é um aluno vinculado a uma turma (SLOT-10 e SLOT-19).
- Itens de menu de professor e aluno: as páginas deles chegam nos próximos cards (SLOT-9, SLOT-13 em diante). Neste card o menu deles tem só o Dashboard.
- Dashboard diferente por papel (SLOT-27).
- Impedir que o último admin exclua a própria conta em Configurações.

## Decisões

- Card piloto do fluxo SDD (SLOT-5): segue o fluxo inteiro, com spec, plano e tasks.

## Suposições

- Na tela os papéis aparecem como Admin, Professor e Aluno. No código e no banco: `admin`, `professor`, `student`.
- Cada linha da página de usuários tem um seletor de papel e um botão Salvar. O papel não muda só de escolher no seletor, para evitar troca sem querer.
- Na linha do próprio admin o seletor fica desabilitado com a marca "você". O backend recusa a mudança mesmo assim, com a mensagem "Você não pode tirar o seu próprio papel de admin."
- Um admin pode promover outra pessoa a admin e rebaixar outro admin. Como ninguém tira o próprio admin, sempre sobra pelo menos um.
- A busca é um campo só, procura parte do nome ou do e-mail sem diferenciar maiúsculas, e a lista vem em ordem de nome, 15 por página.
- A lista mostra nome, e-mail e papel. Nada de editar nome ou e-mail dos outros por aqui.
- O papel aparece no menu do usuário (embaixo do nome e do e-mail), para dar para conferir qual papel está ativo.
- Os usuários do seeder se chamam Admin, Professor e Aluno, com a senha `password` e o e-mail já verificado. O "Test User" do starter kit sai do seeder.
