import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login',      name: 'login',      component: () => import('../views/LoginView.vue'),      meta: { guest: true }        },
    { path: '/',           name: 'dashboard',  component: () => import('../views/DashboardView.vue'),  meta: { requiresAuth: true } },
    { path: '/products',   name: 'products',   component: () => import('../views/ProductsView.vue'),   meta: { requiresAuth: true } },
    { path: '/categories', name: 'categories', component: () => import('../views/CategoriesView.vue'), meta: { requiresAuth: true } },
    { path: '/sales',      name: 'sales',      component: () => import('../views/SalesView.vue'),      meta: { requiresAuth: true } },
    { path: '/inventory',  name: 'inventory',  component: () => import('../views/InventoryView.vue'),  meta: { requiresAuth: true } },
  ]
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth && !auth.isAuthenticated) next('/login')
  else if (to.meta.guest && auth.isAuthenticated) next('/')
  else next()
})

export default router
