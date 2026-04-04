import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../components/pages/HomePage.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../components/pages/LoginPage.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../components/pages/RegisterPage.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/invitation/accept',
      name: 'accept-invitation',
      component: () => import('../components/pages/AcceptInvitationPage.vue'),
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('../components/pages/ForgotPasswordPage.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: () => import('../components/pages/ResetPasswordPage.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../components/pages/DashboardPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/cats',
      name: 'cats',
      component: () => import('../components/pages/CatsPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/chip-types',
      name: 'chip-types',
      component: () => import('../components/pages/ChipTypesPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/pet-sitters',
      name: 'pet-sitters',
      component: () => import('../components/pages/PetSittersPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('../components/pages/ProfilePage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/cats/:catId/calendar',
      redirect: (to) => ({ path: '/dashboard', query: { cat: String(to.params.catId) } }),
    },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }
})

export default router
