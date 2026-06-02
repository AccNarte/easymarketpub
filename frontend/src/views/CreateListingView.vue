<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()

const form = ref({
  title: '',
  description: '',
  price: '',
  category_id: '',
  location: '',
  images: []
})

const categories = ref([])
const imagesPreviews = ref([])
const loading = ref(false)
const error = ref(null)

// Autocompletion de la ville
const cityQuery = ref('')
const citySuggestions = ref([])
const showCitySuggestions = ref(false)

const searchCities = async () => {
  if (cityQuery.value.length < 2) {
    citySuggestions.value = []
    return
  }

  try {
    const response = await fetch(
      `https://geo.api.gouv.fr/communes?nom=${encodeURIComponent(cityQuery.value)}&fields=nom,codesPostaux&limit=8`
    )
    const data = await response.json()
    citySuggestions.value = data.map(city => ({
      name: city.nom,
      postalCode: city.codesPostaux?.[0] || ''
    }))
    showCitySuggestions.value = true
  } catch {
    citySuggestions.value = []
  }
}

const selectCity = (city) => {
  form.value.location = `${city.name} (${city.postalCode})`
  cityQuery.value = form.value.location
  showCitySuggestions.value = false
}

const handleImages = (e) => {
  const files = Array.from(e.target.files)
  form.value.images = [...form.value.images, ...files]

  files.forEach(file => {
    const reader = new FileReader()
    reader.onload = (e) => imagesPreviews.value.push(e.target.result)
    reader.readAsDataURL(file)
  })
}

const removeImage = (index) => {
  form.value.images.splice(index, 1)
  imagesPreviews.value.splice(index, 1)
}

const submit = async () => {
  loading.value = true
  error.value = null

  try {
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('description', form.value.description)
    formData.append('price', form.value.price)
    formData.append('category_id', form.value.category_id)
    formData.append('location', form.value.location)

    form.value.images.forEach(img => {
      formData.append('images[]', img)
    })

    const response = await api.post('/listings', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    router.push({ name: 'listing-detail', params: { id: response.data.id } })
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de la création'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const response = await api.get('/categories')
    categories.value = response.data
  } catch {
    console.error('Erreur chargement catégories')
  }
})
</script>

<template>
  <div class="container-page max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Déposer une annonce</h1>

    <form @submit.prevent="submit" class="card p-6 space-y-5">
      <div v-if="error" class="p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
        {{ error }}
      </div>

      <!-- Titre -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
        <input v-model="form.title" type="text" class="input" required placeholder="Ex: iPhone 14 Pro Max 256Go" />
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
        <textarea v-model="form.description" rows="4" class="input" required placeholder="Décrivez votre article en détail..."></textarea>
      </div>

      <!-- Price + Category -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Prix (EUR) *</label>
          <input v-model="form.price" type="number" min="0" step="1" class="input" required placeholder="0" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie *</label>
          <select v-model="form.category_id" class="input" required>
            <option value="">Sélectionner...</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Location with autocomplete -->
      <div class="relative">
        <label class="block text-sm font-medium text-gray-700 mb-1">Localisation *</label>
        <input
          v-model="cityQuery"
          @input="searchCities"
          @focus="showCitySuggestions = citySuggestions.length > 0"
          @blur="setTimeout(() => showCitySuggestions = false, 200)"
          type="text"
          class="input"
          required
          placeholder="Tapez une ville..."
        />

        <!-- Suggestions dropdown -->
        <div v-if="showCitySuggestions && citySuggestions.length" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto">
          <button
            v-for="city in citySuggestions"
            :key="city.name + city.postalCode"
            type="button"
            @mousedown="selectCity(city)"
            class="w-full px-4 py-2 text-left hover:bg-gray-50 flex justify-between"
          >
            <span class="font-medium">{{ city.name }}</span>
            <span class="text-gray-500">{{ city.postalCode }}</span>
          </button>
        </div>
      </div>

      <!-- Photos -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Photos</label>
        <input type="file" accept="image/*" multiple @change="handleImages" class="hidden" id="images" />
        <label for="images" class="block border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-primary-500 transition">
          <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          <span class="text-sm text-gray-500">Cliquez pour ajouter des photos</span>
        </label>

        <div v-if="imagesPreviews.length" class="mt-3 flex gap-2 flex-wrap">
          <div v-for="(img, i) in imagesPreviews" :key="i" class="relative">
            <img :src="img" class="w-20 h-20 object-cover rounded-lg" />
            <button type="button" @click="removeImage(i)" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center">
              ×
            </button>
          </div>
        </div>
      </div>

      <button type="submit" :disabled="loading" class="btn btn-primary w-full py-3">
        {{ loading ? 'Publication...' : 'Publier l\'annonce' }}
      </button>
    </form>
  </div>
</template>
