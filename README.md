# Car Rental App

![CI](https://github.com/ItamarJuniorDEV/car-rental/actions/workflows/ci.yml/badge.svg)
![License](https://img.shields.io/badge/License-MIT-green)

API REST e interface web para gerenciamento de locadora de veículos.

Desenvolvi esse projeto a partir de uma conversa com o dono de uma locadora pequena que controlava tudo em planilhas do Excel — qual carro estava disponível, quem tinha alugado, quilometragem de saída e retorno. O sistema nunca chegou a ser implantado, mas serviu de base pra eu estruturar uma aplicação com as preocupações que aparecem em projetos reais: race condition no momento do aluguel, cálculo de multa na devolução, controle de acesso por papel em ambas as camadas e soft delete pra preservar histórico sem perder os dados.

---

## Funcionalidades

- Cadastro de marcas, linhas e veículos com filtros e busca
- Cadastro de clientes com validação de CPF
- Criação de locação com verificação de disponibilidade em transação atômica
- Registro de devolução com cálculo automático de multa por atraso
- Controle de acesso por papel (admin / operador)
- Gerenciamento de usuários pelo painel (criar, promover, revogar papel)
- Soft delete em todas as entidades — histórico preservado
- 81 testes automatizados no backend, 42 no frontend

---

## Telas

**Dashboard** — locações ativas em destaque com alerta visual para devolução em atraso

![Dashboard](images/img-dashboard.png)

**Locações** — criação de locação, registro de devolução e cálculo de multa

![Locações](images/img-locacoes.png)

**Clientes**

![Clientes](images/img-clientes.png)

**Veículos**

![Veículos](images/img-veiculos.png)

**Marcas**

![Marcas](images/img-marcas.png)

**Linhas**

![Linhas](images/img-linhas.png)

**Usuários** — admin pode criar usuários, promover e revogar papel; não pode alterar o próprio papel

![Usuários](images/img-usuarios.png)

---

## Arquitetura

Monorepo com backend e frontend servidos de forma independente.

```
car-rental-app/
├── backend/    # API Laravel 12 (porta 8001)
└── frontend/   # Interface Quasar v2 + Vue 3 (porta 9000)
```

### Visão Geral

```mermaid
flowchart LR
    Browser <-->|HTTP/JSON| SPA
    SPA <-->|HTTP/JSON + Bearer| API
    API <--> Eloquent
    Eloquent <--> PostgreSQL
```

### Fluxo de Requisição

```mermaid
flowchart TD
    Request --> Routes
    Routes --> auth:sanctum
    auth:sanctum --> Controller
    Controller --> Eloquent
    Eloquent --> PostgreSQL
```

### Fluxo de Autenticação

```mermaid
sequenceDiagram
    participant C as Cliente
    participant A as API
    participant D as Database

    C->>A: POST /api/login (email, senha)
    A->>D: Busca usuário por email
    D-->>A: Dados do usuário
    A->>A: Compara senha com bcrypt
    A->>A: Gera token Sanctum
    A-->>C: { token }

    Note over C,A: Requisições autenticadas
    C->>A: GET /api/me (Bearer token)
    A->>A: Valida token
    A->>A: Verifica papel
    A-->>C: { id, name, email, role }
```

---

## Modelo de Dados

```mermaid
erDiagram
    USERS {
        int id PK
        string name
        string email UK
        string password
        enum role
        timestamp created_at
        timestamp updated_at
    }

    BRANDS {
        int id PK
        string name UK
        string image
        timestamp deleted_at
    }

    LINES {
        int id PK
        int brand_id FK
        string name
        string image
        int door_count
        int seats
        boolean air_bag
        boolean abs
        timestamp deleted_at
    }

    CARS {
        int id PK
        int line_id FK
        string plate UK
        boolean available
        int km
        timestamp deleted_at
    }

    CLIENTS {
        int id PK
        string name
        string cpf UK
        string email UK
        string phone
        timestamp deleted_at
    }

    RENTALS {
        int id PK
        int client_id FK
        int car_id FK
        datetime period_start_date
        datetime period_expected_end_date
        datetime period_actual_end_date
        decimal daily_rate
        int initial_km
        int final_km
        timestamp deleted_at
    }

    BRANDS ||--o{ LINES : possui
    LINES ||--o{ CARS : possui
    CARS ||--o{ RENTALS : participa
    CLIENTS ||--o{ RENTALS : realiza
```

---

## Tecnologias

**Backend**

- PHP 8.3 + Laravel 12
- Laravel Sanctum — autenticação por Bearer token
- Dedoc Scramble — documentação OpenAPI a partir do código
- PostgreSQL 16 em produção, SQLite in-memory para testes
- PHPUnit 11, Laravel Pint

**Frontend**

- Vue 3 + Quasar v2 (Composition API, `<script setup>`)
- Pinia — estado global com persistência em localStorage
- Axios com interceptors — injeção de token e redirecionamento automático em 401
- Vitest + @vue/test-utils

**CI**

- GitHub Actions — Pint + PHPStan + testes a cada push em `master`

---

## Instalação

**Pré-requisitos:** PHP 8.3+, Composer, Node 20+, PostgreSQL 16

Crie o banco de dados:

```bash
createdb locadora
```

**Backend:**

```bash
cd backend
composer install
cp .env.example .env
# edite DB_PASSWORD no .env
php artisan key:generate
php artisan migrate --seed
```

**Frontend:**

```bash
cd frontend
npm install
```

Crie `frontend/.env`:

```env
API_URL=http://localhost:8001/api
```

---

## Rodando

**Terminal 1:**

```bash
cd backend && php artisan serve --port=8001
```

**Terminal 2:**

```bash
cd frontend && npm run dev
```

Acesse `http://localhost:9000`

Login padrão: `admin@locadora.com` / `senha123`

---

## Testes

```bash
# backend — SQLite in-memory, não precisa do PostgreSQL configurado
cd backend && php artisan test

# frontend
cd frontend && npm test
```

---

## Controle de Acesso

| Papel | Permissões |
|-------|------------|
| `admin` | Acesso total — marcas, linhas, veículos, clientes, locações e gerenciamento de usuários |
| `operador` | Clientes e locações. Sem acesso ao cadastro de frota nem à gestão de usuários |

O papel padrão ao registrar via `/api/register` é `operador`. Novos usuários com papel específico só podem ser criados por um admin via painel ou `POST /api/users`.

Um admin não pode alterar o próprio papel — a proteção está no backend e é refletida na interface (o botão de ação não aparece para o próprio usuário autenticado).

---

## Regras de Negócio

- Carro indisponível → 422 ao tentar criar locação
- Disponibilidade verificada dentro de uma transação para evitar race condition
- `final_km` menor que `initial_km` → 422
- Data de devolução anterior à data de início → 422
- Deletar carro ou cliente com locação em aberto → 422
- Multa por atraso: `dias_de_atraso × diária × 0.5`
- Todas as entidades usam soft delete — nada é removido fisicamente do banco

---

## Licença

MIT
