<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useWalletStore } from '../stores/wallet'
import api from '../services/api'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const wallet = useWalletStore()

const listing = ref(null)
const loading = ref(true)
const error = ref(null)
const currentImageIndex = ref(0)
const buying = ref(false)
const buyError = ref(null)
const showMessageModal = ref(false)
const messageText = ref('')
const sendingMessage = ref(false)

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const backendUrl = import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://localhost:8000'

const getImageUrl = (image) => {
  if (!image) return null
  let url = typeof image === 'string' ? image : (image.medium_url || image.original_url || image.thumbnail_url)
  if (!url) return null
  if (url.startsWith('/')) url = backendUrl + url
  return url
}

const images = computed(() => {
  if (!listing.value?.images?.length) return []
  return listing.value.images.map(img => getImageUrl(img)).filter(Boolean)
})

const currentImage = computed(() => images.value[currentImageIndex.value] || null)

const isOwner = computed(() => auth.user?.id === listing.value?.user_id)
const isSold = computed(() => listing.value?.status === 'sold' || listing.value?.sold_at)
const canBuy = computed(() => auth.isAuthenticated && !isOwner.value && !isSold.value)

const nextImage = () => {
  currentImageIndex.value = (currentImageIndex.value + 1) % images.value.length
}

const prevImage = () => {
  currentImageIndex.value = currentImageIndex.value > 0
    ? currentImageIndex.value - 1
    : images.value.length - 1
}

const buyListing = async () => {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: route.fullPath } })
    return
  }

  if (wallet.balance < listing.value.price) {
    buyError.value = 'Solde insuffisant. Rechargez votre portefeuille.'
    return
  }

  if (!confirm(`Confirmer l'achat de "${listing.value.title}" pour ${formatPrice(listing.value.price)} ?`)) {
    return
  }

  buying.value = true
  buyError.value = null

  try {
    await api.post(`/listings/${listing.value.id}/buy`)
    await wallet.fetchBalance()
    listing.value.status = 'sold'
    listing.value.sold_at = new Date()
    alert('Achat effectué avec succès !')
  } catch (e) {
    buyError.value = e.response?.data?.message || 'Erreur lors de l\'achat'
  } finally {
    buying.value = false
  }
}

const openMessageModal = () => {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: route.fullPath } })
    return
  }
  showMessageModal.value = true
}

const sendMessage = async () => {
  if (!messageText.value.trim()) return

  sendingMessage.value = true
  try {
    const response = await api.post('/conversations', {
      listing_id: listing.value.id,
      message: messageText.value
    })
    showMessageModal.value = false
    messageText.value = ''
    router.push({ name: 'conversation', params: { id: response.data.conversation.uuid } })
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur lors de l\'envoi')
  } finally {
    sendingMessage.value = false
  }
}

onMounted(async () => {
  try {
    const response = await api.get(`/listings/${route.params.id}`)
    listing.value = response.data
    if (auth.isAuthenticated) {
      await wallet.fetchBalance()
    }
  } catch (e) {
    error.value = 'Annonce introuvable'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="container-page">
    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-16">
      <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="text-center py-16">
      <p class="text-gray-500 mb-4">{{ error }}</p>
      <button @click="router.back()" class="btn btn-secondary">Retour</button>
    </div>

    <!-- Content -->
    <div v-else-if="listing" class="grid lg:grid-cols-5 gap-6">
      <!-- Left: Images + Description -->
      <div class="lg:col-span-3 space-y-4">
        <!-- Main Image -->
        <div class="card overflow-hidden">
          <div class="aspect-[4/3] bg-gray-100 relative">
            <!-- Sold badge -->
            <div v-if="isSold" class="absolute top-4 left-4 z-10 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-medium">
              Vendu
            </div>

            <img v-if="currentImage" :src="currentImage" :alt="listing.title" class="w-full h-full object-contain" />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
              <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>

            <!-- Navigation arrows -->
            <template v-if="images.length > 1">
              <button @click="prevImage" class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <button @click="nextImage" class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </template>
          </div>

          <!-- Thumbnails -->
          <div v-if="images.length > 1" class="p-3 flex gap-2 overflow-x-auto border-t border-gray-100">
            <button
              v-for="(img, i) in images"
              :key="i"
              @click="currentImageIndex = i"
              :class="['w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 border-2', currentImageIndex === i ? 'border-primary-500' : 'border-transparent']"
            >
              <img :src="img" class="w-full h-full object-cover" />
            </button>
          </div>
        </div>

        <!-- Description -->
        <div class="card p-5">
          <h2 class="font-semibold text-gray-900 mb-3">Description</h2>
          <p class="text-gray-600 whitespace-pre-line">{{ listing.description }}</p>
        </div>
      </div>

      <!-- Right: Info + Seller -->
      <div class="lg:col-span-2 space-y-4">
        <!-- Price & Info -->
        <div class="card p-5">
          <p class="text-2xl font-bold text-primary-600 mb-1">
            {{ formatPrice(listing.price) }}
          </p>
          <h1 class="text-lg font-semibold text-gray-900 mb-3">
            {{ listing.title }}
          </h1>

          <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>{{ listing.location }}</span>
          </div>

          <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ formatDate(listing.created_at) }}</span>
          </div>

          <span class="badge-gray">
            {{ listing.category?.name || 'Autre' }}
          </span>
        </div>

        <!-- Buy Section -->
        <div v-if="!isOwner" class="card p-5">
          <div v-if="isSold" class="text-center text-gray-500">
            Cette annonce a été vendue
          </div>
          <div v-else>
            <div v-if="buyError" class="mb-3 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
              {{ buyError }}
            </div>

            <button
              @click="buyListing"
              :disabled="buying"
              class="btn w-full py-3 text-white font-semibold rounded-lg"
              style="background-color: #22c55e;"
            >
              {{ buying ? 'Achat en cours...' : `Acheter pour ${formatPrice(listing.price)}` }}
            </button>

            <p v-if="auth.isAuthenticated" class="text-xs text-gray-500 text-center mt-2">
              Solde actuel : {{ formatPrice(wallet.balance) }}
            </p>
          </div>
        </div>

        <!-- Seller -->
        <div class="card p-5">
          <h3 class="font-semibold text-gray-900 mb-4">Vendeur</h3>
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-lg font-semibold">
              {{ listing.user?.name?.[0]?.toUpperCase() || '?' }}
            </div>
            <div>
              <p class="font-medium text-gray-900">{{ listing.user?.name || 'Utilisateur' }}</p>
              <p class="text-sm text-gray-500">Membre depuis {{ new Date(listing.user?.created_at).getFullYear() || '2026' }}</p>
            </div>
          </div>

          <button v-if="!isOwner" @click="openMessageModal" class="btn btn-primary w-full">
            Contacter le vendeur
          </button>
        </div>

        <!-- Safety Tips -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
          <h4 class="font-medium text-amber-800 mb-2">Conseils de sécurité</h4>
          <ul class="text-sm text-amber-700 space-y-1">
            <li>• Privilégiez la remise en main propre</li>
            <li>• Ne payez jamais à l'avance</li>
            <li>• Vérifiez l'article avant d'acheter</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Message Modal -->
    <div v-if="showMessageModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-md p-5">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Envoyer un message</h3>

        <textarea
          v-model="messageText"
          rows="4"
          class="input mb-4"
          placeholder="Bonjour, je suis intéressé par votre annonce..."
        ></textarea>

        <div class="flex gap-3">
          <button @click="showMessageModal = false" class="btn btn-secondary flex-1">
            Annuler
          </button>
          <button @click="sendMessage" :disabled="sendingMessage || !messageText.trim()" class="btn btn-primary flex-1">
            {{ sendingMessage ? 'Envoi...' : 'Envoyer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
