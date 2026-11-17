# SLOT-30 · Atualizar o Pint para corrigir o pre-push no Windows

Card: https://slotgestao.atlassian.net/browse/SLOT-30
Tipo: Chore · Branch: `chore/SLOT-30-atualiza-pint` · Base: `develop`
Aprovado por:

## Objetivo

No Windows, o hook `pre-push` falha sempre na etapa do Pint, mesmo com o código formatado. O Pint 1.32.2 termina a checagem, não acha a própria tela de resumo (`View [summary] not found`) e sai com erro, e o hook mostra "Laravel Pint encontrou problemas de formatação". Quem desenvolve no Windows não consegue dar push. O Pint 1.32.3 corrige isso ("resolve views inside the phar on Windows").

## Regras de negócio

- R1: O `composer.lock` usa o Pint 1.32.3 ou mais novo.
- R2: As regras de formatação não mudam: o `pint.json` e o restante do `composer.lock` ficam como estão.

## Cenários

```gherkin
Cenário: Push no Windows com o código formatado
  Dado que estou no Windows com o projeto instalado pelo composer install
  Quando faço push de uma branch com o código formatado
  Então a etapa do Pint passa e o hook segue para o PHPStan e os testes
```

## Critérios de aceite

- [ ] `composer install` instala o `laravel/pint` 1.32.3.
- [ ] `./vendor/bin/pint --test` termina sem o erro `View [summary] not found` no Windows.
- [ ] `composer ci:check` verde.

## Fora do escopo

- Atualizar outras dependências.
- Mudar o hook `pre-push` ou o `pint.json`.

## Decisões

- Causa: no Windows, o Pint 1.32.2 monta o caminho das telas com `\` e a checagem de segurança de caminhos `phar://` do Laravel recusa o arquivo. A correção está só no Pint, por isso basta atualizar o pacote.

## Suposições

- Só o Pint é atualizado (`composer update laravel/pint`). O Composer 2.11 também regrava o `content-hash` e o `plugin-api-version` do lock; os outros pacotes ficam na mesma versão.
- Chore pequena, com mudança óbvia: pula o `plan.md`.
