<script setup>
import { ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useWalletStore } from '../stores/wallet'

const auth = useAuthStore()
const wallet = useWalletStore()
const router = useRouter()

const mobileMenuOpen = ref(false)

const formatBalance = (balance) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(balance)
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/')
}

watch(() => auth.isAuthenticated, (isAuth) => {
  if (isAuth) wallet.fetchBalance()
}, { immediate: true })
</script>

<template>
  <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <div class="flex justify-between h-16">
        <!-- Logo + Nav -->
        <div class="flex items-center gap-8">
          <RouterLink to="/" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-lg">E</span>
            </div>
            <span class="text-xl font-bold text-gray-900 hidden sm:block">EasyMarket</span>
          </RouterLink>

          <!-- Desktop Navigation -->
          <div class="hidden md:flex items-center gap-1">
            <RouterLink to="/annonces" class="btn btn-ghost">
              Annonces
            </RouterLink>
            <RouterLink v-if="auth.isAuthenticated" to="/publier" class="btn btn-ghost">
              Vendre
            </RouterLink>
          </div>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-3">
          <template v-if="auth.isAuthenticated">
            <!-- Balance -->
            <RouterLink to="/portefeuille" class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
              <span class="font-semibold">{{ formatBalance(wallet.balance) }}</span>
            </RouterLink>

            <!-- User Menu -->
            <div class="relative group">
              <button class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100">
                <div class="w-8 h-8 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center font-medium">
                  {{ auth.user?.name?.[0]?.toUpperCase() || '?' }}
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Dropdown -->
              <div class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                <div class="px-4 py-2 border-b border-gray-100">
                  <p class="font-medium text-gray-900 truncate">{{ auth.user?.name }}</p>
                  <p class="text-sm text-gray-500 truncate">{{ auth.user?.email }}</p>
                </div>
                <RouterLink to="/portefeuille" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 sm:hidden">
                  <span>Portefeuille</span>
                  <span class="ml-auto font-medium text-green-600">{{ formatBalance(wallet.balance) }}</span>
                </RouterLink>
                <RouterLink to="/profil" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                  Mon profil
                </RouterLink>
                <RouterLink to="/messages" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                  Messages
                </RouterLink>
                <RouterLink to="/portefeuille" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                  Mon portefeuille
                </RouterLink>
                <template v-if="auth.isAdmin">
                  <hr class="my-1">
                  <RouterLink to="/admin" class="block px-4 py-2 text-sm text-red-700 font-medium hover:bg-red-50">
                    Administration
                  </RouterLink>
                </template>
                <hr class="my-1">
                <button @click="handleLogout" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                  Déconnexion
                </button>
              </div>
            </div>
          </template>

          <template v-else>
            <RouterLink to="/connexion" class="btn btn-ghost hidden sm:flex">
              Connexion
            </RouterLink>
            <RouterLink to="/inscription" class="btn btn-primary">
              S'inscrire
            </RouterLink>
          </template>

          <!-- Mobile menu button -->
          <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div v-if="mobileMenuOpen" class="md:hidden py-4 border-t border-gray-100">
        <div class="space-y-1">
          <RouterLink to="/annonces" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-lg hover:bg-gray-100">
            Parcourir les annonces
          </RouterLink>
          <RouterLink v-if="auth.isAuthenticated" to="/publier" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-lg hover:bg-gray-100">
            Déposer une annonce
          </RouterLink>
          <RouterLink v-if="auth.isAuthenticated" to="/messages" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-lg hover:bg-gray-100">
            Messages
          </RouterLink>
          <RouterLink v-if="!auth.isAuthenticated" to="/connexion" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-lg hover:bg-gray-100">
            Connexion
          </RouterLink>
        </div>
      </div>
    </div>
  </nav>
</template>
