<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

const props = defineProps({
  listing: {
    type: Object,
    required: true
  }
})

const backendUrl = import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://localhost:8000'

const imageUrl = computed(() => {
  const thumb = props.listing.thumbnail
  if (!thumb) return null
  if (thumb.startsWith('/')) return backendUrl + thumb
  return thumb
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', minimumFractionDigits: 0 }).format(price)
}

const timeAgo = (date) => {
  const seconds = Math.floor((new Date() - new Date(date)) / 1000)
  if (seconds < 60) return "À l'instant"
  const minutes = Math.floor(seconds / 60)
  if (minutes < 60) return `Il y a ${minutes}min`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `Il y a ${hours}h`
  const days = Math.floor(hours / 24)
  if (days < 7) return `Il y a ${days}j`
  return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
}
</script>

<template>
  <RouterLink :to="{ name: 'listing-detail', params: { id: listing.id } }" class="card-hover group block overflow-hidden">
    <!-- Image -->
    <div class="aspect-square bg-gray-100 relative overflow-hidden">
      <img
        v-if="imageUrl"
        :src="imageUrl"
        :alt="listing.title"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
      />
      <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
    </div>

    <!-- Contenu -->
    <div class="p-3">
      <p class="text-lg font-bold text-gray-900">{{ formatPrice(listing.price) }}</p>
      <h3 class="text-sm text-gray-700 truncate mt-0.5">{{ listing.title }}</h3>
      <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
        <span>{{ listing.location }}</span>
        <span>•</span>
        <span>{{ timeAgo(listing.created_at) }}</span>
      </div>
    </div>
  </RouterLink>
</template>
