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
| Qualidade | Laravel Pint, Larastan nível 6, Rector |
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

## Arquitetura e decisões técnicas

A aplicação usa os componentes nativos do Laravel diretamente nos fluxos simples de CRUD e concentra regras de negócio que exigem coordenação de estado no `RentalService`. Isso evita adicionar uma camada de repositório apenas para encapsular chamadas simples do Eloquent, mantendo a separação onde ela agrega valor ao domínio.

As transições de estado de uma locação são executadas em transações. Na criação, o veículo é carregado com `lockForUpdate()` antes da checagem de disponibilidade, e a quilometragem inicial é obtida do próprio registro bloqueado no banco, não do payload do cliente.

Na devolução, locação e veículo são bloqueados e atualizados na mesma transação. Uma locação finalizada não pode ser finalizada novamente. Ao remover uma locação, a disponibilidade do veículo é recalculada considerando outras locações ativas.

Autenticação é feita com Sanctum e autorização com Policies. O ambiente de desenvolvimento também impede lazy loading para tornar consultas inesperadas mais visíveis durante a evolução do código.

## Qualidade de código

O projeto mantém os checks de qualidade como parte do pipeline, não apenas como ferramentas de uso local:

- **Laravel Pint** para consistência de estilo;
- **Larastan/PHPStan nível 6** para análise estática de controllers, models, relações, requests e regras de domínio;
- **Rector em dry-run** para detectar oportunidades seguras de modernização sem aplicar refatorações automáticas no CI;
- **PHPUnit** para regressão funcional e regras de negócio;
- **Composer Audit** e **Gitleaks** no workflow de segurança.

Para executar a mesma sequência localmente:

```bash
cd backend
composer quality
```

O comando executa estilo, análise estática, Rector em modo de verificação e testes.

## Integridade das locações

A multa por atraso é calculada no `RentalService` com base nos dias excedentes e no valor da diária. A integridade da disponibilidade do veículo é tratada junto das operações de locação para que mudanças relacionadas não sejam persistidas pela metade.

## Testes

```bash
cd backend
php artisan test
```

A suíte cobre autenticação, autorização, CRUDs principais, regras de locação, validações, rate limiting e cabeçalhos de segurança.

## Segurança

O repositório mantém workflows separados para CI e segurança. O pipeline principal valida `composer.json`, executa Pint, Larastan nível 6, Rector em dry-run e a suíte de testes. O fluxo de segurança executa auditoria das dependências do Composer e varredura de segredos com Gitleaks.

## Licença

MIT
