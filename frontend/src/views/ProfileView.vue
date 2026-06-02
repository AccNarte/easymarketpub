<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useWalletStore } from '../stores/wallet'
import api from '../services/api'
import ListingCard from '../components/ListingCard.vue'

const auth = useAuthStore()
const wallet = useWalletStore()

const myListings = ref([])
const loading = ref(true)

const formatPrice = (n) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(n)

onMounted(async () => {
  try {
    const [listingsRes] = await Promise.all([
      api.get('/user/listings'),
      wallet.fetchBalance()
    ])
    myListings.value = listingsRes.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="container-page max-w-4xl">
    <!-- Profile Header -->
    <div class="card p-6 mb-6">
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-2xl font-semibold">
          {{ auth.user?.name?.[0]?.toUpperCase() }}
        </div>
        <div class="flex-1">
          <h1 class="text-xl font-bold text-gray-900">{{ auth.user?.name }}</h1>
          <p class="text-gray-500">{{ auth.user?.email }}</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid sm:grid-cols-3 gap-4 mb-6">
      <RouterLink to="/portefeuille" class="card p-4 hover:border-primary-300 transition flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-xl">
          💰
        </div>
        <div>
          <p class="text-sm text-gray-500">Mon solde</p>
          <p class="text-lg font-bold text-gray-900">{{ formatPrice(wallet.balance) }}</p>
        </div>
      </RouterLink>

      <RouterLink to="/publier" class="card p-4 hover:border-primary-300 transition flex items-center gap-4">
        <div class="w-12 h-12 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-xl">
          +
        </div>
        <div>
          <p class="text-sm text-gray-500">Nouvelle annonce</p>
          <p class="text-lg font-semibold text-gray-900">Vendre un article</p>
        </div>
      </RouterLink>

      <RouterLink to="/messages" class="card p-4 hover:border-primary-300 transition flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-xl">
          💬
        </div>
        <div>
          <p class="text-sm text-gray-500">Messagerie</p>
          <p class="text-lg font-semibold text-gray-900">Mes conversations</p>
        </div>
      </RouterLink>
    </div>

    <!-- My Listings -->
    <div class="mb-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Mes annonces</h2>

      <div v-if="loading" class="flex justify-center py-8">
        <div class="w-6 h-6 border-2 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <div v-else-if="myListings.length === 0" class="card p-8 text-center">
        <p class="text-gray-500 mb-4">Vous n'avez pas encore d'annonce</p>
        <RouterLink to="/publier" class="btn btn-primary">
          Créer ma première annonce
        </RouterLink>
      </div>

      <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <ListingCard v-for="listing in myListings" :key="listing.id" :listing="listing" />
      </div>
    </div>
  </div>
</template>
