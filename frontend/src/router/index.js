import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('../views/HomeView.vue')
  },
  {
    path: '/annonces',
    name: 'listings',
    component: () => import('../views/ListingsView.vue')
  },
  {
    path: '/annonce/:id',
    name: 'listing-detail',
    component: () => import('../views/ListingDetailView.vue')
  },
  {
    path: '/produits',
    name: 'produits',
    component: () => import('../views/ProduitView.vue')
  },
  {
    path: '/publier',
    name: 'create-listing',
    component: () => import('../views/CreateListingView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/connexion',
    name: 'login',
    component: () => import('../views/LoginView.vue')
  },
  {
    path: '/inscription',
    name: 'register',
    component: () => import('../views/RegisterView.vue')
  },
  {
    path: '/profil',
    name: 'profile',
    component: () => import('../views/ProfileView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/portefeuille',
    name: 'wallet',
    component: () => import('../views/WalletView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/messages',
    name: 'messages',
    component: () => import('../views/MessagesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/messages/:id([a-f0-9-]{36})',
    name: 'conversation',
    component: () => import('../views/ConversationView.vue'),
    meta: { requiresAuth: true }
  },
  // Pages informatives
  {
    path: '/a-propos',
    name: 'about',
    component: () => import('../views/pages/AboutView.vue')
  },
  {
    path: '/comment-ca-marche',
    name: 'how-it-works',
    component: () => import('../views/pages/HowItWorksView.vue')
  },
  {
    path: '/contact',
    name: 'contact',
    component: () => import('../views/pages/ContactView.vue')
  },
  {
    path: '/mentions-legales',
    name: 'legal',
    component: () => import('../views/pages/LegalView.vue')
  },
  {
    path: '/cgv',
    name: 'terms',
    component: () => import('../views/pages/TermsView.vue')
  },
  {
    path: '/confidentialite',
    name: 'privacy',
    component: () => import('../views/pages/PrivacyView.vue')
  },
  // Admin
  {
    path: '/admin',
    component: () => import('../views/admin/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      { path: '', redirect: '/admin/utilisateurs' },
      {
        path: 'utilisateurs',
        name: 'admin-users',
        component: () => import('../views/admin/AdminUsersView.vue')
      },
      {
        path: 'annonces',
        name: 'admin-listings',
        component: () => import('../views/admin/AdminListingsView.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  }
})

router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  if (to.meta.requiresAdmin) {
    const auth = useAuthStore()
    if (!auth.user) {
      await auth.fetchUser()
    }
    if (!auth.isAdmin) {
      return next({ name: 'home' })
    }
  }

  next()
})

export default router
