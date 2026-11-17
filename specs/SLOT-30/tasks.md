# Tasks · SLOT-30

- [x] T1 · Atualizar o Pint para 1.32.3 no `composer.lock`
    - Teste: `./vendor/bin/pint --test` no terminal do Windows termina sem `View [summary] not found`; `composer ci:check` verde (Cenário: Push no Windows com o código formatado)
    - Arquivos: `composer.lock`
