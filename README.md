# Car Rental API

> API REST em Laravel 12 para gestão de locadora de veículos: frota, clientes, locações com cálculo de multa e controle de acesso por papel.

![CI](https://github.com/ItamarJuniorDEV/car-rental-api/actions/workflows/ci.yml/badge.svg)
![License](https://img.shields.io/badge/License-MIT-green)

## Índice

- [Sobre](#sobre)
- [Funcionalidades](#funcionalidades)
- [Stack](#stack)
- [Como rodar](#como-rodar)
- [Variáveis de ambiente](#variáveis-de-ambiente)
- [Modelo de dados](#modelo-de-dados)
- [Documentação da API](#documentação-da-api)
- [Testes](#testes)
- [Decisões técnicas](#decisões-técnicas)
- [Licença](#licença)

## Sobre

Desenvolvi esse projeto a partir de uma conversa com o dono de uma locadora pequena que controlava tudo em planilhas do Excel: qual carro estava disponível, quem tinha alugado, quilometragem de saída e retorno. O sistema nunca chegou a ser implantado, mas serviu de base pra eu estruturar uma API com as preocupações que aparecem em projetos reais: race condition no momento do aluguel, cálculo de multa na devolução, controle de acesso por papel e soft delete pra preservar o histórico.

## Funcionalidades

- Cadastro de marcas, linhas e veículos com filtro e paginação
- Cadastro de clientes com validação de CPF
- Criação de locação com verificação de disponibilidade em transação atômica
- Registro de devolução com cálculo automático de multa por atraso
- Controle de acesso por papel (admin / operador)
- Gerenciamento de usuários (criar, promover, revogar papel)
- Soft delete em todas as entidades

## Stack

| Camada | Tecnologia |
|--------|------------|
| Linguagem | PHP 8.3 |
| Framework | Laravel 12 |
| Autenticação | Laravel Sanctum (Bearer token) |
| Banco | PostgreSQL 16 (SQLite in-memory nos testes) |
| Documentação | Dedoc Scramble (OpenAPI a partir do código) |
| Testes | PHPUnit 11 |
| Estilo | Laravel Pint |
| Infra | Docker, GitHub Actions |

## Como rodar

Pré-requisitos: PHP 8.3+, Composer, PostgreSQL 16 (ou Docker).

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8001
```

API em `http://localhost:8001/api`. Login padrão do seeder: `admin@locadora.com` / `senha123`.

Com Docker (`docker-compose.yml` sobe PostgreSQL 16 + backend):

```bash
docker compose up -d
```

## Variáveis de ambiente

Principais variáveis do `backend/.env`:

| Variável | Descrição | Padrão |
|----------|-----------|--------|
| `APP_ENV` | Ambiente | `local` |
| `DB_CONNECTION` | Driver do banco | `pgsql` |
| `DB_*` | Credenciais do PostgreSQL | (obrigatória) |
| `CORS_ALLOWED_ORIGINS` | Origens liberadas no CORS | (obrigatória) |

## Modelo de dados

Hierarquia de cadastro: cada marca tem várias linhas, cada linha tem vários carros, e cada carro pode ter várias locações.

- `brands` tem muitas `lines`
- `lines` tem muitos `cars`
- `cars` tem muitas `rentals`
- `clients` tem muitas `rentals`
- `users` com papel `admin` ou `operador`
- `rentals` guarda período (início, previsto e devolução real), diária e quilometragem inicial e final
- `deleted_at` (soft delete) em brands, lines, cars, clients e rentals

## Documentação da API

Todas as rotas exigem `Authorization: Bearer <token>`, exceto registro e login.

| Método | Rota | Acesso | Descrição |
|--------|------|--------|-----------|
| POST | `/api/register` | público | Cadastra usuário (papel `operador`) |
| POST | `/api/login` | público | Autentica e retorna token |
| POST | `/api/logout` | autenticado | Revoga o token atual |
| GET | `/api/me` | autenticado | Dados do usuário autenticado |
| GET | `/api/brands` `/api/lines` `/api/cars` | autenticado | Lista (paginado, com filtro) |
| POST/PUT/DELETE | `/api/brands` `/api/lines` `/api/cars` | admin | Gerencia frota |
| GET/POST/PUT/DELETE | `/api/clients` `/api/rentals` | operador / admin | CRUD de clientes e locações |
| GET/POST | `/api/users` | admin | Lista e cria usuários |
| PATCH | `/api/users/{id}/role` | admin | Promove ou revoga papel |

Documentação interativa (Scramble) disponível em `/docs/api`.

### Formato de resposta

Sucesso:

```json
{
  "message": "Marca encontrada com sucesso!",
  "data": { "id": 1, "name": "Toyota" }
}
```

Listagem paginada inclui o bloco `pagination` (`total`, `per_page`, `current_page`, `last_page`).

Erro de validação (422):

```json
{
  "message": "The given data was invalid.",
  "errors": { "name": ["O campo name é obrigatório."] }
}
```

## Testes

```bash
cd backend && php artisan test
```

81 testes de feature e unidade, em SQLite in-memory (não precisa do PostgreSQL configurado). Cobrem CRUD, regras de negócio, autorização por papel, cabeçalhos de segurança, rate limiting e prevenção de IDOR.

## Decisões técnicas

- **Transação com lock pessimista na criação da locação.** Dois operadores podem tentar alugar o mesmo carro ao mesmo tempo. A disponibilidade é checada dentro de `DB::transaction` com `lockForUpdate()` no carro, evitando double-booking.

- **Login em tempo constante.** O `login` roda dentro de um `Timebox` e compara a senha contra um hash placeholder quando o e-mail não existe, mantendo o tempo de resposta constante. Some-se a isso a mensagem de erro uniforme ("Credenciais inválidas") para não revelar se um e-mail está cadastrado (anti timing attack e anti enumeração de usuários).

- **Autorização por Policy.** Cada model tem sua Policy; o `authorize()` no controller garante que operador e admin só fazem o que o papel permite, inclusive bloqueando acesso direto por URL.

- **Throttle separado.** `throttle:login` mais apertado no registro e login (anti brute-force) e `throttle:api` no restante.

- **Soft delete.** Marcas, carros, clientes e locações usam `deleted_at`, preservando o histórico em vez de apagar fisicamente.

- **Multa por atraso** calculada como `dias_de_atraso x diária x 0.5`, isolada no `RentalService`.

## Licença

MIT
