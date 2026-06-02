<script setup>
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../services/api'

const conversations = ref([])
const loading = ref(true)
const searchQuery = ref('')

const backendUrl = import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://localhost:8000'

const getImageUrl = (url) => {
  if (!url) return null
  if (url.startsWith('/')) return backendUrl + url
  return url
}

const filteredConversations = computed(() => {
  if (!searchQuery.value) return conversations.value
  const query = searchQuery.value.toLowerCase()
  return conversations.value.filter(c =>
    c.other_user?.name?.toLowerCase().includes(query) ||
    c.listing?.title?.toLowerCase().includes(query)
  )
})

const timeAgo = (date) => {
  if (!date) return ''
  const seconds = Math.floor((new Date() - new Date(date)) / 1000)
  if (seconds < 60) return "À l'instant"
  const minutes = Math.floor(seconds / 60)
  if (minutes < 60) return `${minutes} min`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours}h`
  const days = Math.floor(hours / 24)
  if (days < 7) return `${days}j`
  return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
}

onMounted(async () => {
  try {
    const response = await api.get('/conversations')
    conversations.value = response.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="min-h-[calc(100vh-64px)] bg-gradient-to-b from-slate-50 to-slate-100">
    <div class="max-w-2xl mx-auto">
      <!-- En-tete -->
      <div class="sticky top-16 z-10 bg-white/80 backdrop-blur-lg border-b border-slate-200 px-4 py-4">
        <h1 class="text-2xl font-bold text-slate-900 mb-4">Messages</h1>

        <!-- Recherche -->
        <div class="relative">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Rechercher une conversation..."
            class="w-full pl-10 pr-4 py-2.5 bg-slate-100 border-0 rounded-xl text-slate-900 placeholder-slate-500 focus:ring-2 focus:ring-primary-500 focus:bg-white transition"
          />
        </div>
      </div>

      <!-- Chargement -->
      <div v-if="loading" class="flex justify-center py-20">
        <div class="w-10 h-10 border-3 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <!-- Etat vide -->
      <div v-else-if="conversations.length === 0" class="px-4 py-20 text-center">
        <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center">
          <svg class="w-12 h-12 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
        </div>
        <h2 class="text-xl font-semibold text-slate-900 mb-2">Aucune conversation</h2>
        <p class="text-slate-500 mb-6">Contactez un vendeur pour démarrer une conversation</p>
        <RouterLink to="/annonces" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition shadow-lg shadow-primary-600/25">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          Parcourir les annonces
        </RouterLink>
      </div>

      <!-- Liste des conversations -->
      <div v-else class="divide-y divide-slate-100">
        <RouterLink
          v-for="conv in filteredConversations"
          :key="conv.id"
          :to="{ name: 'conversation', params: { id: conv.uuid } }"
          class="flex items-center gap-4 px-4 py-4 hover:bg-white/80 transition group"
        >
          <!-- Avatar -->
          <div class="relative flex-shrink-0">
            <div class="w-14 h-14 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white text-xl font-semibold shadow-lg shadow-primary-600/20">
              {{ conv.other_user?.name?.[0]?.toUpperCase() || '?' }}
            </div>
            <!-- Pastille en ligne (decorative) -->
            <div class="absolute bottom-0 right-0 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></div>
          </div>

          <!-- Contenu -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2 mb-1">
              <h3 class="font-semibold text-slate-900 truncate">
                {{ conv.other_user?.name }}
              </h3>
              <span class="text-xs text-slate-400 flex-shrink-0">
                {{ conv.last_message ? timeAgo(conv.last_message.created_at) : '' }}
              </span>
            </div>

            <p class="text-sm text-primary-600 truncate mb-0.5">
              {{ conv.listing?.title }}
            </p>

            <p class="text-sm text-slate-500 truncate">
              {{ conv.last_message?.content || 'Nouvelle conversation' }}
            </p>
          </div>

          <!-- Unread Badge -->
          <div v-if="conv.unread_count > 0" class="flex-shrink-0">
            <span class="inline-flex items-center justify-center min-w-[24px] h-6 px-2 bg-primary-600 text-white text-xs font-bold rounded-full shadow-lg shadow-primary-600/30">
              {{ conv.unread_count }}
            </span>
          </div>

          <!-- Arrow -->
          <svg class="w-5 h-5 text-slate-300 group-hover:text-slate-400 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </RouterLink>
      </div>

      <!-- No results -->
      <div v-if="!loading && filteredConversations.length === 0 && searchQuery" class="px-4 py-12 text-center">
        <p class="text-slate-500">Aucune conversation trouvée pour "{{ searchQuery }}"</p>
      </div>
    </div>
  </div>
</template>
