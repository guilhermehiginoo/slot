# Slot

Sistema de agendamento de monitorias, desenvolvido para a disciplina de Gestão e Metodologias Ágeis de Projetos (UFRN / IMD).

## Stack

Laravel 13, PHP 8.5, React 19 com Inertia, TypeScript e Tailwind (starter kit oficial do Laravel).

## Rodando localmente (Herd)

```bash
herd isolate 8.5
composer install
cp .env.example .env
php artisan key:generate
php artisan install:features
php artisan migrate
npm install
npm run prepare
composer run dev
```

O app fica em http://slot.test.

## Time

Guilherme Higino (Product Owner e dev), Adrian Felipe (Scrum Master e dev), Renan Avelino (dev), Maurício Costa (dev) e Marcos Antonio (QA).
