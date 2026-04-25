# Locadora API

![CI](https://github.com/ItamarJuniorDEV/car-rental-app/actions/workflows/ci.yml/badge.svg)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

API REST para gerenciamento de locadora de veículos em Laravel 12. Cada recurso tem Policy dedicada para autorização, Form Request para validação e API Resource para serialização. A documentação OpenAPI é gerada automaticamente pelo Scramble a partir do código.

A parte mais interessante foi modelar a locação: verificação de disponibilidade dentro de uma transação para evitar que dois operadores aluguem o mesmo carro ao mesmo tempo, e o cálculo de multa na devolução com base nos dias de atraso.

---

## Tecnologias

- PHP 8.3 + Laravel 12
- Laravel Sanctum (autenticação por Bearer token)
- Dedoc Scramble (OpenAPI gerado a partir do código)
- PostgreSQL 16 em produção, SQLite in-memory nos testes
- PHPUnit 11
- Laravel Pint

---

## Estrutura

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/    # Camada HTTP, fina
│   │   ├── Requests/       # Validação de entrada (Form Request)
│   │   ├── Resources/      # Formatação da resposta JSON
│   │   ├── Middleware/     # SecurityHeaders, throttle
│   │   └── Resources/
│   ├── Models/
│   ├── Policies/           # Autorização por recurso
│   ├── Services/           # Regra de negócio (RentalService)
│   └── Providers/
├── config/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
└── tests/
    ├── Feature/
    └── Unit/
```

---

## Instalação

**Pré-requisitos:** PHP 8.4+, Composer, PostgreSQL 16

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Configure o banco no `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=locadora
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

Execute as migrations com seed:

```bash
php artisan migrate --seed
```

Suba o servidor:

```bash
php artisan serve --port=8001
```

API disponível em `http://localhost:8001/api`

---

## Autenticação

Todas as rotas — exceto `/register` e `/login` — exigem o header:

```
Authorization: Bearer {token}
```

O token é gerado no registro e regenerado a cada login. Ao fazer logout, todos os tokens do usuário são revogados (Sanctum).

Existem dois papéis:

| Papel | Permissões |
|-------|------------|
| `admin` | Acesso total, incluindo exclusão de marcas, linhas e veículos e gerenciamento de usuários |
| `operador` | Clientes e locações. Sem acesso ao cadastro de frota nem à gestão de usuários |

### Registrar

```http
POST /api/register
Content-Type: application/json

{
    "name": "Carlos Mendes",
    "email": "carlos@locadora.com",
    "password": "minhasenha",
    "password_confirmation": "minhasenha"
}
```

Resposta: `{ "token": "..." }` — papel padrão: `operador`.

### Login

```http
POST /api/login
Content-Type: application/json

{
    "email": "carlos@locadora.com",
    "password": "minhasenha"
}
```

Resposta: `{ "token": "..." }`

### Logout

```http
POST /api/logout
Authorization: Bearer {token}
```

### Usuário autenticado

```http
GET /api/me
Authorization: Bearer {token}
```

```json
{
    "id": 1,
    "name": "Carlos Mendes",
    "email": "carlos@locadora.com",
    "role": "operador"
}
```

---

## Endpoints

### Usuários (somente admin)

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/users` | Lista todos os usuários |
| POST | `/api/users` | Cria um novo usuário |
| PATCH | `/api/users/{id}/role` | Altera o papel do usuário |

**Criar usuário:**

```http
POST /api/users
Authorization: Bearer {token}

{
    "name": "Novo Operador",
    "email": "operador@locadora.com",
    "password": "senha123",
    "role": "operador"
}
```

**Alterar papel:**

```http
PATCH /api/users/{id}/role
Authorization: Bearer {token}

{
    "role": "admin"
}
```

Um admin não pode alterar o próprio papel — retorna 422.

---

### Marcas

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/brands` | Lista marcas (paginado, 15 por página) |
| GET | `/api/brands?name=toy` | Busca por nome |
| GET | `/api/brands/{id}` | Detalhe da marca |
| POST | `/api/brands` | Criar marca (admin) |
| PUT | `/api/brands/{id}` | Atualizar marca (admin) |
| DELETE | `/api/brands/{id}` | Remover marca (admin, soft delete) |

**Criar marca:**

```http
POST /api/brands
Authorization: Bearer {token}

{
    "name": "Toyota",
    "image": "toyota.png"
}
```

---

### Linhas (modelos)

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/lines` | Lista linhas |
| GET | `/api/lines?brand_id=1` | Filtra por marca |
| GET | `/api/lines/{id}` | Detalhe |
| POST | `/api/lines` | Criar linha (admin) |
| PUT | `/api/lines/{id}` | Atualizar (admin) |
| DELETE | `/api/lines/{id}` | Remover (admin, soft delete) |

**Criar linha:**

```http
POST /api/lines
Authorization: Bearer {token}

{
    "brand_id": 1,
    "name": "Corolla",
    "image": "corolla.png",
    "door_count": 4,
    "seats": 5,
    "air_bag": true,
    "abs": true
}
```

---

### Veículos

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/cars` | Lista veículos |
| GET | `/api/cars?available=1` | Somente disponíveis |
| GET | `/api/cars?plate=abc` | Busca por placa |
| GET | `/api/cars/{id}` | Detalhe |
| POST | `/api/cars` | Cadastrar veículo (admin) |
| PUT | `/api/cars/{id}` | Atualizar (admin) |
| DELETE | `/api/cars/{id}` | Remover (admin, bloqueia se houver locação ativa) |

**Cadastrar veículo:**

```http
POST /api/cars
Authorization: Bearer {token}

{
    "line_id": 1,
    "plate": "ABC-1D23",
    "available": true,
    "km": 15000
}
```

---

### Clientes

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/clients` | Lista clientes |
| GET | `/api/clients?name=maria` | Busca por nome |
| GET | `/api/clients/{id}` | Detalhe |
| POST | `/api/clients` | Cadastrar cliente |
| PUT | `/api/clients/{id}` | Atualizar |
| DELETE | `/api/clients/{id}` | Remover (bloqueia se houver locação ativa) |

**Cadastrar cliente:**

```http
POST /api/clients
Authorization: Bearer {token}

{
    "name": "Maria Oliveira",
    "cpf": "123.456.789-00",
    "email": "maria@email.com",
    "phone": "(51) 99999-1234"
}
```

CPF deve estar no formato `###.###.###-##`.

---

### Locações

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/rentals` | Lista locações |
| GET | `/api/rentals/{id}` | Detalhe |
| POST | `/api/rentals` | Criar locação |
| PUT | `/api/rentals/{id}` | Registrar devolução |
| DELETE | `/api/rentals/{id}` | Remover |

**Criar locação:**

```http
POST /api/rentals
Authorization: Bearer {token}

{
    "client_id": 1,
    "car_id": 1,
    "period_start_date": "2026-03-01 08:00:00",
    "period_expected_end_date": "2026-03-05 08:00:00",
    "daily_rate": 200.00,
    "initial_km": 15000
}
```

A criação verifica disponibilidade do carro e atualiza `available = false` dentro de uma transação.

**Registrar devolução:**

```http
PUT /api/rentals/{id}
Authorization: Bearer {token}

{
    "period_actual_end_date": "2026-03-07 08:00:00",
    "final_km": 15800
}
```

Se a devolução for após a data prevista, `late_fee` é calculado automaticamente (50% da diária por dia de atraso). O carro volta para `available = true` e o km é atualizado.

**Exemplo de resposta:**

```json
{
    "data": {
        "id": 1,
        "period_start_date": "2026-03-01 08:00:00",
        "period_expected_end_date": "2026-03-05 08:00:00",
        "period_actual_end_date": "2026-03-07 08:00:00",
        "daily_rate": 200,
        "initial_km": 15000,
        "final_km": 15800,
        "late_fee": 200,
        "total": 1000,
        "client": {
            "id": 1,
            "name": "Maria Oliveira",
            "cpf": "123.456.789-00",
            "email": "maria@email.com",
            "phone": "(51) 99999-1234"
        },
        "car": {
            "id": 1,
            "plate": "ABC-1D23",
            "available": true,
            "km": 15800,
            "line": { "..." : "..." }
        }
    }
}
```

---

## Regras de Negócio

- Carro indisponível → 422 ao tentar criar locação
- Disponibilidade verificada dentro de transação para evitar race condition
- `final_km` menor que `initial_km` → 422
- Data de devolução anterior à data de início → 422
- Deletar carro ou cliente com locação em aberto → 422
- Multa por atraso: `dias_de_atraso × diária × 0.5`
- Todas as entidades usam soft delete — nada é removido fisicamente do banco

---

## Erros comuns

**401 em todas as rotas:** token ausente ou expirado. Faça login novamente.

**403 em rotas de admin:** usuário autenticado não tem papel `admin`.

**422 ao criar cliente:** CPF deve estar no formato `000.000.000-00` e email precisa ser único.

**422 ao criar locação:** verifique se o `car_id` existe e se `available` é `true`.

**Migration falhou:** rode `php artisan migrate:fresh --seed` para recriar o banco do zero.

---

## Testes

Os testes rodam em SQLite in-memory — não precisam do PostgreSQL configurado.

```bash
php artisan test
```

Ou diretamente com PHPUnit:

```bash
./vendor/bin/phpunit
```

Cobertura: autenticação (register, login, logout, 401), CRUD de marcas com restrição por papel, ciclo completo de locação (criar, devolver, multa por atraso, validações de km e data, proteção de delete com locação ativa) e gerenciamento de usuários.

---

## Scripts

| Comando | Descrição |
|---------|-----------|
| `php artisan serve --port=8001` | Servidor de desenvolvimento |
| `php artisan test` | Executa os testes |
| `php artisan migrate --seed` | Roda migrations e popula o banco |
| `php artisan migrate:fresh --seed` | Recria o banco do zero com seed |
| `./vendor/bin/pint` | Formata o código (PSR-12) |

A documentação OpenAPI fica em `/docs/api` quando a aplicação está rodando.

---

## Licença

MIT
