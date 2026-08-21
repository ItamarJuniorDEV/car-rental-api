# Car Rental API

> API REST para gestão de locadora de veículos, com controle de frota, clientes, locações, devoluções e permissões por papel.

![CI](https://github.com/ItamarJuniorDEV/car-rental-api/actions/workflows/ci.yml/badge.svg)
![License](https://img.shields.io/badge/License-MIT-green)

## Funcionalidades

- cadastro de marcas, linhas e veículos;
- cadastro de clientes com validação de CPF;
- criação de locações com verificação de disponibilidade;
- devolução com atualização de quilometragem e cálculo de multa por atraso;
- controle de acesso para administradores e operadores;
- gerenciamento de usuários;
- preservação de histórico com soft delete.

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.3, Laravel 12 |
| Autenticação | Laravel Sanctum |
| Banco | PostgreSQL 16 |
| Documentação | Dedoc Scramble / OpenAPI |
| Testes | PHPUnit 11, SQLite in-memory |
| Qualidade | Laravel Pint, Larastan |
| Infra | Docker, GitHub Actions |

## Como rodar

Pré-requisitos: PHP 8.3+, Composer e PostgreSQL.

```bash
git clone https://github.com/ItamarJuniorDEV/car-rental-api.git
cd car-rental-api/backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8001
```

A API fica disponível em `http://localhost:8001/api` e a documentação interativa em `http://localhost:8001/docs/api`.

O seeder cria um usuário administrativo para desenvolvimento:

```text
admin@locadora.com
senha123
```

O `docker-compose.yml` inclui PostgreSQL 16 e o backend PHP-FPM para uso em ambiente containerizado.

## Autenticação e autorização

As rotas protegidas utilizam tokens Bearer do Laravel Sanctum. O acesso às operações é controlado por Policies e pelos papéis `admin` e `operador`.

Registro e login são públicos. As demais rotas exigem autenticação conforme a permissão da operação.

## Integridade das locações

A criação de uma locação bloqueia o registro do veículo com `lockForUpdate()` dentro de uma transação antes de alterar sua disponibilidade.

A devolução atualiza a locação e o estado do veículo na mesma transação. Uma locação já finalizada não pode ser finalizada novamente. Ao remover uma locação, a disponibilidade do veículo é recalculada considerando outras locações ativas.

A multa por atraso é calculada no `RentalService` com base nos dias excedentes e no valor da diária.

## Testes

```bash
cd backend
php artisan test
```

A suíte cobre autenticação, autorização, CRUDs principais, regras de locação, validações, rate limiting e cabeçalhos de segurança.

O pipeline de CI também executa o Laravel Pint antes dos testes.

## Segurança

O repositório mantém workflows separados para testes e verificações de segurança. O fluxo de segurança executa auditoria das dependências do Composer e varredura de segredos com Gitleaks.

## Licença

MIT
