<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const conversation = ref(null)
const loading = ref(true)
const newMessage = ref('')
const sending = ref(false)
const messagesContainer = ref(null)
const fileInput = ref(null)
const selectedImage = ref(null)
const previewUrl = ref(null)

const backendUrl = import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://localhost:8000'

const getImageUrl = (url) => {
  if (!url) return null
  if (url.startsWith('/')) return backendUrl + url
  return url
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price)
}

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
}

const formatDate = (date) => {
  const d = new Date(date)
  const today = new Date()
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)

  if (d.toDateString() === today.toDateString()) return "Aujourd'hui"
  if (d.toDateString() === yesterday.toDateString()) return "Hier"
  return d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' })
}

const groupedMessages = computed(() => {
  if (!conversation.value?.messages) return []

  const groups = []
  let currentDate = null

  conversation.value.messages.forEach(msg => {
    const msgDate = formatDate(msg.created_at)
    if (msgDate !== currentDate) {
      currentDate = msgDate
      groups.push({ type: 'date', date: msgDate })
    }
    groups.push({ type: 'message', ...msg })
  })

  return groups
})

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const onFilePick = (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  if (!file.type.startsWith('image/')) {
    alert('Seules les images sont autorisées.')
    return
  }
  if (file.size > 5 * 1024 * 1024) {
    alert('Image trop volumineuse (max 5 Mo).')
    return
  }
  selectedImage.value = file
  previewUrl.value = URL.createObjectURL(file)
}

const clearImage = () => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  selectedImage.value = null
  previewUrl.value = null
  if (fileInput.value) fileInput.value.value = ''
}

const sendMessage = async () => {
  if ((!newMessage.value.trim() && !selectedImage.value) || sending.value) return

  const content = newMessage.value
  const image = selectedImage.value
  const localPreview = previewUrl.value
  newMessage.value = ''
  sending.value = true

  const tempMsg = {
    id: Date.now(),
    sender_id: auth.user.id,
    content,
    image_url: localPreview,
    created_at: new Date().toISOString(),
    sender: auth.user,
    pending: true
  }
  conversation.value.messages.push(tempMsg)
  scrollToBottom()

  try {
    let response
    if (image) {
      const formData = new FormData()
      if (content) formData.append('message', content)
      formData.append('image', image)
      response = await api.post(
        `/conversations/${conversation.value.uuid}/messages`,
        formData,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      )
    } else {
      response = await api.post(`/conversations/${conversation.value.uuid}/messages`, {
        message: content
      })
    }

    const idx = conversation.value.messages.findIndex(m => m.id === tempMsg.id)
    if (idx !== -1) {
      conversation.value.messages[idx] = response.data
    }
    clearImage()
  } catch (e) {
    conversation.value.messages = conversation.value.messages.filter(m => m.id !== tempMsg.id)
    newMessage.value = content
    selectedImage.value = image
    previewUrl.value = localPreview
    alert(e.response?.data?.message || 'Erreur lors de l\'envoi')
  } finally {
    sending.value = false
  }
}

const handleKeydown = (e) => {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault()
    sendMessage()
  }
}

onMounted(async () => {
  try {
    const response = await api.get(`/conversations/${route.params.id}`)
    conversation.value = response.data
    await scrollToBottom()
  } catch (e) {
    router.push({ name: 'messages' })
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="h-[calc(100vh-64px)] flex flex-col bg-slate-100">
    <!-- Chargement -->
    <div v-if="loading" class="flex-1 flex items-center justify-center">
      <div class="w-10 h-10 border-3 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <template v-else-if="conversation">
      <!-- En-tete -->
      <div class="bg-white border-b border-slate-200 px-4 py-3 flex items-center gap-4 shadow-sm">
        <!-- Bouton retour -->
        <RouterLink to="/messages" class="p-2 -ml-2 rounded-full hover:bg-slate-100 transition">
          <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </RouterLink>

        <!-- Infos utilisateur -->
        <div class="flex items-center gap-3 flex-1 min-w-0">
          <div class="relative">
            <div class="w-11 h-11 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white font-semibold shadow">
              {{ conversation.other_user?.name?.[0]?.toUpperCase() || '?' }}
            </div>
            <div class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></div>
          </div>
          <div class="min-w-0">
            <h2 class="font-semibold text-slate-900 truncate">{{ conversation.other_user?.name }}</h2>
            <p class="text-xs text-emerald-600">En ligne</p>
          </div>
        </div>

        <!-- Plus d'options -->
        <button class="p-2 rounded-full hover:bg-slate-100 transition">
          <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
          </svg>
        </button>
      </div>

      <!-- Bandeau d'info annonce -->
      <RouterLink
        :to="{ name: 'listing-detail', params: { id: conversation.listing?.id } }"
        class="bg-gradient-to-r from-primary-50 to-primary-100 border-b border-primary-200 px-4 py-3 flex items-center gap-4 hover:from-primary-100 hover:to-primary-150 transition"
      >
        <div class="w-14 h-14 bg-white rounded-xl overflow-hidden shadow flex-shrink-0">
          <img
            v-if="conversation.listing?.thumbnail"
            :src="getImageUrl(conversation.listing.thumbnail)"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-medium text-slate-900 truncate">{{ conversation.listing?.title }}</p>
          <p class="text-lg font-bold text-primary-600">{{ formatPrice(conversation.listing?.price) }}</p>
        </div>
        <svg class="w-5 h-5 text-primary-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </RouterLink>

      <!-- Messages -->
      <div
        ref="messagesContainer"
        class="flex-1 overflow-y-auto px-4 py-6 space-y-4"
        style="background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%); background-attachment: fixed;"
      >
        <template v-for="item in groupedMessages" :key="item.type === 'date' ? item.date : item.id">
          <!-- Separateur de date -->
          <div v-if="item.type === 'date'" class="flex justify-center">
            <span class="px-4 py-1.5 bg-white/80 backdrop-blur-sm text-slate-500 text-xs font-medium rounded-full shadow-sm">
              {{ item.date }}
            </span>
          </div>

          <!-- Bulle de message -->
          <div
            v-else
            :class="[
              'flex',
              item.sender_id === auth.user?.id ? 'justify-end' : 'justify-start'
            ]"
          >
            <div
              :class="[
                'max-w-[80%] sm:max-w-[70%] shadow-sm overflow-hidden',
                item.sender_id === auth.user?.id
                  ? 'bg-primary-600 text-white rounded-2xl rounded-br-md'
                  : 'bg-white text-slate-900 rounded-2xl rounded-bl-md',
                item.pending ? 'opacity-70' : ''
              ]"
            >
              <a v-if="item.image_url" :href="getImageUrl(item.image_url)" target="_blank" rel="noopener" class="block">
                <img :src="getImageUrl(item.image_url)" class="w-full max-h-80 object-cover" alt="Image jointe" />
              </a>
              <div :class="['px-4 py-2.5', item.image_url && !item.content ? 'py-2' : '']">
                <p v-if="item.content" class="whitespace-pre-wrap break-words leading-relaxed">{{ item.content }}</p>
                <div :class="['flex items-center justify-end gap-1', item.content ? 'mt-1' : '', item.sender_id === auth.user?.id ? 'text-primary-200' : 'text-slate-400']">
                  <span class="text-xs">{{ formatTime(item.created_at) }}</span>
                  <svg v-if="item.sender_id === auth.user?.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Zone de saisie -->
      <div class="bg-white border-t border-slate-200 px-4 py-3">
        <!-- Apercu de l'image -->
        <div v-if="previewUrl" class="max-w-4xl mx-auto mb-2 relative inline-block">
          <img :src="previewUrl" class="h-24 rounded-lg object-cover" alt="Aperçu" />
          <button
            @click="clearImage"
            class="absolute -top-2 -right-2 w-6 h-6 bg-slate-800 text-white rounded-full flex items-center justify-center text-xs shadow"
            aria-label="Retirer l'image"
          >
            ✕
          </button>
        </div>

        <div class="flex items-end gap-3 max-w-4xl mx-auto">
          <!-- Input fichier cache -->
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="onFilePick"
          />

          <!-- Bouton piece jointe -->
          <button
            type="button"
            @click="fileInput?.click()"
            class="p-2.5 text-slate-400 hover:text-primary-600 hover:bg-slate-100 rounded-full transition flex-shrink-0"
            aria-label="Joindre une image"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
          </button>

          <!-- Saisie de texte -->
          <div class="flex-1 relative">
            <textarea
              v-model="newMessage"
              @keydown="handleKeydown"
              rows="1"
              placeholder="Écrivez votre message..."
              class="w-full px-4 py-3 bg-slate-100 border-0 rounded-2xl text-slate-900 placeholder-slate-500 resize-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition"
              style="max-height: 120px;"
            ></textarea>
          </div>

          <!-- Bouton d'envoi -->
          <button
            @click="sendMessage"
            :disabled="sending || (!newMessage.trim() && !selectedImage)"
            :class="[
              'p-3 rounded-full transition flex-shrink-0 shadow-lg',
              (newMessage.trim() || selectedImage)
                ? 'bg-primary-600 text-white hover:bg-primary-700 shadow-primary-600/30'
                : 'bg-slate-200 text-slate-400'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
          </button>
        </div>

        <!-- Indicateur de saisie (a brancher) -->
        <div class="h-6 flex items-center px-2 text-xs text-slate-400">
          <!-- {{ otherUser?.name }} est en train d'écrire... -->
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
textarea {
  field-sizing: content;
  min-height: 48px;
}
</style>
