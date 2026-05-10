# Locadora Frontend

![Vue](https://img.shields.io/badge/Vue-3-4FC08D?logo=vue.js&logoColor=white)
![Quasar](https://img.shields.io/badge/Quasar-2-1976D2?logo=quasar&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-5-646CFF?logo=vite&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

Interface web do sistema de locadora de veículos. Consome a API REST do backend Laravel via Axios com autenticação por Bearer token.

O ponto mais relevante da implementação foi o controle de acesso em duas camadas: o guard do Vue Router bloqueia a navegação para rotas restritas antes de renderizar a página, e a sidebar omite os itens de menu baseando-se no papel armazenado na store — evitando que o operador sequer veja as opções de administração.

---

## Tecnologias

- Vue 3 + Quasar v2 (Composition API, `<script setup>`)
- Pinia — estado global com persistência em localStorage
- Axios com interceptors — token injetado automaticamente, redirect para `/login` em 401
- Vitest + @vue/test-utils

---

## Estrutura

```
frontend/src/
├── boot/
│   ├── axios.js        # instância axios + interceptors
│   └── pinia.js
├── layouts/
│   ├── MainLayout.vue  # sidebar + header + logout
│   └── AuthLayout.vue  # wrapper para a tela de login
├── pages/
│   ├── LoginPage.vue
│   ├── DashboardPage.vue
│   ├── BrandsPage.vue
│   ├── LinesPage.vue
│   ├── CarsPage.vue
│   ├── ClientsPage.vue
│   ├── RentalsPage.vue
│   └── UsersPage.vue
├── router/
│   ├── routes.js
│   └── index.js        # guard global (requiresAuth, adminOnly)
├── services/           # uma função por recurso da API
├── stores/
│   └── auth.js         # token + user + isAdmin (computed)
└── utils/
    ├── formatDate.js
    └── formatCurrency.js
```

---

## Instalação

**Pré-requisitos:** Node 20+

```bash
cd frontend
npm install
```

Crie o arquivo `.env` na raiz do frontend:

```env
API_URL=http://localhost:8001/api
```

---

## Rodando

O backend precisa estar rodando em `http://localhost:8001`.

```bash
npm run dev
```

Disponível em `http://localhost:9000`

Login padrão: `admin@locadora.com` / `senha123`

---

## Testes

```bash
npm test
```

Os testes cobrem a auth store (setAuth, clear, isAdmin, isAuthenticated), o rental service (mocks de axios para POST/PUT/DELETE), os utilitários de formatação e o componente de confirmação de exclusão.

---

## Páginas e acesso

| Rota | Acesso | Descrição |
|------|--------|-----------|
| `/login` | público | Autenticação |
| `/dashboard` | todos | Métricas e locações ativas |
| `/clientes` | todos | CRUD de clientes |
| `/locacoes` | todos | Criar locação, registrar devolução |
| `/marcas` | admin | CRUD de marcas |
| `/linhas` | admin | CRUD de linhas |
| `/veiculos` | admin | CRUD de veículos |
| `/usuarios` | admin | Gerenciamento de usuários |

Tentativa de acesso direto a rotas restritas pela URL redireciona para `/dashboard`.

---

## Autenticação e estado

O fluxo de login é em dois passos: `POST /api/login` retorna apenas o token, depois `GET /api/me` retorna os dados do usuário. O token é definido na store antes do segundo request para que o interceptor já o inclua no header.

O token e os dados do usuário ficam no `localStorage`. Na inicialização do app, a store lê o localStorage e reconstrói o estado sem precisar fazer login novamente.

Em qualquer resposta 401, o interceptor do Axios chama `auth.clear()` e redireciona para `/login`.

---

## Controle de acesso no frontend

O guard global do Vue Router verifica:

```
requiresAuth → usuário autenticado?  não → /login
adminOnly    → usuário é admin?      não → /dashboard
login        → já autenticado?       sim → /dashboard
```

A sidebar usa `auth.isAdmin` para exibir ou ocultar os itens de administração. As ações destrutivas (deletar, revogar papel) têm um segundo passo de confirmação via dialog.

---

## Scripts

| Comando | Descrição |
|---------|-----------|
| `npm run dev` | Servidor de desenvolvimento (porta 9000) |
| `npm test` | Executa os testes com Vitest |
| `npm run lint` | ESLint |
| `npm run build` | Build de produção |

---

## Licença

MIT
