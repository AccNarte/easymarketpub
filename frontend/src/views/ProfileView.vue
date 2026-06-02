<script setup>
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useWalletStore } from '../stores/wallet'
import api from '../services/api'
import ListingCard from '../components/ListingCard.vue'

const auth = useAuthStore()
const wallet = useWalletStore()

const myListings = ref([])
const myPurchases = ref([])
const mySales = ref([])
const loading = ref(true)
const activeTab = ref('purchases')

const formatPrice = (n) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(n)
const formatDate = (d) => new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })

const shippingLabel = (s) => ({
  not_shipped: 'Non expédié',
  shipped: 'Expédié',
  delivered: 'Livré',
}[s] || s)

const markDelivered = async (purchase) => {
  if (!confirm('Confirmer la réception du colis ?')) return
  try {
    const { data } = await api.post(`/purchases/${purchase.id}/deliver`)
    const idx = myPurchases.value.findIndex(p => p.id === purchase.id)
    if (idx !== -1) myPurchases.value[idx] = data
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur')
  }
}

const markShipped = async (sale) => {
  if (!confirm('Confirmer l\'expédition du colis ?')) return
  try {
    const { data } = await api.post(`/sales/${sale.id}/ship`)
    const idx = mySales.value.findIndex(s => s.id === sale.id)
    if (idx !== -1) mySales.value[idx] = data
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur')
  }
}

const purchaseCount = computed(() => myPurchases.value.length)
const listingCount = computed(() => myListings.value.length)
const saleCount = computed(() => mySales.value.length)

onMounted(async () => {
  try {
    const [listingsRes, purchasesRes, salesRes] = await Promise.all([
      api.get('/user/listings'),
      api.get('/purchases'),
      api.get('/sales'),
      wallet.fetchBalance(),
    ])
    myListings.value = listingsRes.data
    myPurchases.value = purchasesRes.data
    mySales.value = salesRes.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="container-page max-w-4xl">
    <!-- En-tete du profil -->
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

    <!-- Actions rapides -->
    <div class="grid sm:grid-cols-3 gap-4 mb-6">
      <RouterLink to="/portefeuille" class="card p-4 hover:border-primary-300 transition flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-xl">💰</div>
        <div>
          <p class="text-sm text-gray-500">Mon solde</p>
          <p class="text-lg font-bold text-gray-900">{{ formatPrice(wallet.balance) }}</p>
        </div>
      </RouterLink>

      <RouterLink to="/publier" class="card p-4 hover:border-primary-300 transition flex items-center gap-4">
        <div class="w-12 h-12 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-xl">+</div>
        <div>
          <p class="text-sm text-gray-500">Nouvelle annonce</p>
          <p class="text-lg font-semibold text-gray-900">Vendre un article</p>
        </div>
      </RouterLink>

      <RouterLink to="/messages" class="card p-4 hover:border-primary-300 transition flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-xl">💬</div>
        <div>
          <p class="text-sm text-gray-500">Messagerie</p>
          <p class="text-lg font-semibold text-gray-900">Mes conversations</p>
        </div>
      </RouterLink>
    </div>

    <!-- Onglets -->
    <div class="flex gap-2 border-b border-gray-200 mb-6">
      <button
        @click="activeTab = 'purchases'"
        :class="['pb-3 px-1 text-sm font-medium border-b-2 -mb-px transition',
                 activeTab === 'purchases' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
      >
        Mes achats <span class="ml-1 text-xs text-gray-400">({{ purchaseCount }})</span>
      </button>
      <button
        @click="activeTab = 'sales'"
        :class="['pb-3 px-1 text-sm font-medium border-b-2 -mb-px transition',
                 activeTab === 'sales' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
      >
        Mes ventes <span class="ml-1 text-xs text-gray-400">({{ saleCount }})</span>
      </button>
      <button
        @click="activeTab = 'listings'"
        :class="['pb-3 px-1 text-sm font-medium border-b-2 -mb-px transition',
                 activeTab === 'listings' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
      >
        Mes annonces <span class="ml-1 text-xs text-gray-400">({{ listingCount }})</span>
      </button>
    </div>

    <!-- Chargement -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="w-6 h-6 border-2 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Mes achats -->
    <div v-else-if="activeTab === 'purchases'">
      <div v-if="myPurchases.length === 0" class="card p-8 text-center">
        <p class="text-gray-500 mb-4">Vous n'avez encore rien acheté</p>
        <RouterLink to="/annonces" class="btn btn-primary">Parcourir les annonces</RouterLink>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="p in myPurchases"
          :key="p.id"
          class="card p-4 flex flex-col sm:flex-row sm:items-center gap-4"
        >
          <RouterLink :to="`/annonce/${p.listing.id}`" class="flex-shrink-0">
            <img
              v-if="p.listing?.images?.[0]"
              :src="p.listing.images[0].thumbnail_url"
              :alt="p.listing.title"
              class="w-24 h-24 object-cover rounded-lg"
            />
            <div v-else class="w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center text-2xl">📦</div>
          </RouterLink>

          <div class="flex-1 min-w-0">
            <RouterLink :to="`/annonce/${p.listing.id}`" class="font-semibold text-gray-900 hover:text-primary-600 line-clamp-1">
              {{ p.listing.title }}
            </RouterLink>
            <p class="text-sm text-gray-500 mt-0.5">Acheté le {{ formatDate(p.created_at) }}</p>
            <p class="text-sm font-medium text-gray-900 mt-1">{{ formatPrice(p.amount) }}</p>
          </div>

          <div class="flex flex-col items-end gap-2">
            <!-- Badge animé -->
            <div :class="['shipping-badge', `shipping-${p.shipping_status}`]">
              <span v-if="p.shipping_status === 'not_shipped'" class="dot-pulse"></span>
              <span v-else-if="p.shipping_status === 'shipped'" class="truck-anim">🚚</span>
              <span v-else>✓</span>
              {{ shippingLabel(p.shipping_status) }}
            </div>

            <button
              v-if="p.shipping_status === 'shipped'"
              @click="markDelivered(p)"
              class="text-xs text-primary-600 hover:underline"
            >
              J'ai reçu le colis
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mes ventes -->
    <div v-else-if="activeTab === 'sales'">
      <div v-if="mySales.length === 0" class="card p-8 text-center">
        <p class="text-gray-500 mb-4">Aucune vente pour le moment</p>
        <RouterLink to="/publier" class="btn btn-primary">Déposer une annonce</RouterLink>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="s in mySales"
          :key="s.id"
          class="card p-4 flex flex-col sm:flex-row sm:items-center gap-4"
        >
          <RouterLink :to="`/annonce/${s.listing.id}`" class="flex-shrink-0">
            <img
              v-if="s.listing?.images?.[0]"
              :src="s.listing.images[0].thumbnail_url"
              :alt="s.listing.title"
              class="w-24 h-24 object-cover rounded-lg"
            />
            <div v-else class="w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center text-2xl">📦</div>
          </RouterLink>

          <div class="flex-1 min-w-0">
            <RouterLink :to="`/annonce/${s.listing.id}`" class="font-semibold text-gray-900 hover:text-primary-600 line-clamp-1">
              {{ s.listing.title }}
            </RouterLink>
            <p class="text-sm text-gray-500 mt-0.5">Vendu à {{ s.buyer?.name }} le {{ formatDate(s.created_at) }}</p>
            <p class="text-sm font-medium text-green-600 mt-1">+ {{ formatPrice(s.amount) }}</p>
          </div>

          <div class="flex flex-col items-end gap-2">
            <div :class="['shipping-badge', `shipping-${s.shipping_status}`]">
              <span v-if="s.shipping_status === 'not_shipped'" class="dot-pulse"></span>
              <span v-else-if="s.shipping_status === 'shipped'" class="truck-anim">🚚</span>
              <span v-else>✓</span>
              {{ shippingLabel(s.shipping_status) }}
            </div>

            <button
              v-if="s.shipping_status === 'not_shipped'"
              @click="markShipped(s)"
              class="text-xs px-3 py-1.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition"
            >
              Marquer expédié
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mes annonces -->
    <div v-else-if="activeTab === 'listings'">
      <div v-if="myListings.length === 0" class="card p-8 text-center">
        <p class="text-gray-500 mb-4">Vous n'avez pas encore d'annonce</p>
        <RouterLink to="/publier" class="btn btn-primary">Créer ma première annonce</RouterLink>
      </div>

      <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <ListingCard v-for="listing in myListings" :key="listing.id" :listing="listing" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.shipping-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  white-space: nowrap;
}

.shipping-not_shipped {
  background-color: #fff7ed;
  color: #c2410c;
}

.shipping-shipped {
  background-color: #eff6ff;
  color: #1d4ed8;
}

.shipping-delivered {
  background-color: #f0fdf4;
  color: #15803d;
}

/* Dot orange qui pulse pour "non expédié" */
.dot-pulse {
  width: 0.5rem;
  height: 0.5rem;
  border-radius: 9999px;
  background-color: #fb923c;
  animation: pulse-orange 1.6s ease-in-out infinite;
}

@keyframes pulse-orange {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(251, 146, 60, 0.7);
    transform: scale(1);
  }
  50% {
    box-shadow: 0 0 0 8px rgba(251, 146, 60, 0);
    transform: scale(1.15);
  }
}

/* Camion qui avance pour "expédié" */
.truck-anim {
  display: inline-block;
  animation: truck-move 1.6s ease-in-out infinite;
}

@keyframes truck-move {
  0% { transform: translateX(-3px); }
  50% { transform: translateX(3px); }
  100% { transform: translateX(-3px); }
}
</style>
