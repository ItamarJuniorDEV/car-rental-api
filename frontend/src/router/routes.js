const routes = [
  {
    path: '/',
    component: () => import('src/layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', name: 'dashboard', component: () => import('src/pages/DashboardPage.vue') },
      { path: 'marcas', name: 'brands', component: () => import('src/pages/BrandsPage.vue'), meta: { adminOnly: true } },
      { path: 'linhas', name: 'lines', component: () => import('src/pages/LinesPage.vue'), meta: { adminOnly: true } },
      { path: 'veiculos', name: 'cars', component: () => import('src/pages/CarsPage.vue'), meta: { adminOnly: true } },
      { path: 'usuarios', name: 'users', component: () => import('src/pages/UsersPage.vue'), meta: { adminOnly: true } },
      { path: 'clientes', name: 'clients', component: () => import('src/pages/ClientsPage.vue') },
      { path: 'locacoes', name: 'rentals', component: () => import('src/pages/RentalsPage.vue') },
    ],
  },
  {
    path: '/login',
    component: () => import('src/layouts/AuthLayout.vue'),
    children: [
      { path: '', name: 'login', component: () => import('src/pages/LoginPage.vue') },
    ],
  },
  {
    path: '/:catchAll(.*)*',
    redirect: '/',
  },
]

export default routes
