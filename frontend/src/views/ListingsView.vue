<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useListingsStore } from '../stores/listings'
import ListingCard from '../components/ListingCard.vue'

const route = useRoute()
const router = useRouter()
const store = useListingsStore()

const filters = ref({
  q: '',
  category: '',
  minPrice: '',
  maxPrice: '',
  sort: 'recent'
})

const showFilters = ref(false)

const categories = [
  { name: 'Toutes', slug: '' },
  { name: 'Véhicules', slug: 'vehicules' },
  { name: 'Immobilier', slug: 'immobilier' },
  { name: 'Électronique', slug: 'electronique' },
  { name: 'Mode', slug: 'mode' },
  { name: 'Maison', slug: 'maison' },
  { name: 'Loisirs', slug: 'loisirs' },
]

const activeFiltersCount = computed(() => {
  let count = 0
  if (filters.value.category) count++
  if (filters.value.minPrice) count++
  if (filters.value.maxPrice) count++
  return count
})

const applyFilters = () => {
  const query = {}
  if (filters.value.q) query.q = filters.value.q
  if (filters.value.category) query.category = filters.value.category
  if (filters.value.minPrice) query.min = filters.value.minPrice
  if (filters.value.maxPrice) query.max = filters.value.maxPrice
  if (filters.value.sort !== 'recent') query.sort = filters.value.sort

  router.push({ query })
  showFilters.value = false
}

const clearFilters = () => {
  filters.value = { q: '', category: '', minPrice: '', maxPrice: '', sort: 'recent' }
  router.push({ query: {} })
}

const loadFromQuery = () => {
  filters.value.q = route.query.q || ''
  filters.value.category = route.query.category || ''
  filters.value.minPrice = route.query.min || ''
  filters.value.maxPrice = route.query.max || ''
  filters.value.sort = route.query.sort || 'recent'

  store.fetchListings({
    q: filters.value.q,
    category: filters.value.category,
    min_price: filters.value.minPrice,
    max_price: filters.value.maxPrice,
    sort: filters.value.sort
  })
}

onMounted(loadFromQuery)
watch(() => route.query, loadFromQuery)
</script>

<template>
  <div class="container-page">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Annonces</h1>
        <p class="text-gray-500 text-sm mt-1">{{ store.pagination.total }} résultat(s)</p>
      </div>

      <div class="flex items-center gap-2">
        <!-- Search -->
        <form @submit.prevent="applyFilters" class="flex-1 sm:w-64">
          <input
            v-model="filters.q"
            type="text"
            placeholder="Rechercher..."
            class="input"
          />
        </form>

        <!-- Filter button -->
        <button @click="showFilters = !showFilters" class="btn btn-secondary relative">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
          <span class="hidden sm:inline ml-2">Filtres</span>
          <span v-if="activeFiltersCount" class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-xs rounded-full flex items-center justify-center">
            {{ activeFiltersCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Filters Panel -->
    <div v-if="showFilters" class="card p-4 mb-6">
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Category -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
          <select v-model="filters.category" class="input">
            <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <!-- Min Price -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Prix min</label>
          <input v-model="filters.minPrice" type="number" class="input" placeholder="0 €" />
        </div>

        <!-- Max Price -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Prix max</label>
          <input v-model="filters.maxPrice" type="number" class="input" placeholder="∞" />
        </div>

        <!-- Sort -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Trier par</label>
          <select v-model="filters.sort" class="input">
            <option value="recent">Plus récentes</option>
            <option value="price_asc">Prix croissant</option>
            <option value="price_desc">Prix décroissant</option>
          </select>
        </div>
      </div>

      <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-gray-100">
        <button @click="clearFilters" class="btn btn-ghost text-sm">Réinitialiser</button>
        <button @click="applyFilters" class="btn btn-primary text-sm">Appliquer</button>
      </div>
    </div>

    <!-- Category Pills -->
    <div class="flex gap-2 overflow-x-auto pb-4 mb-4 -mx-4 px-4 sm:mx-0 sm:px-0">
      <button
        v-for="cat in categories"
        :key="cat.slug"
        @click="filters.category = cat.slug; applyFilters()"
        :class="[
          'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition',
          filters.category === cat.slug
            ? 'bg-primary-600 text-white'
            : 'bg-white border border-gray-200 text-gray-700 hover:border-gray-300'
        ]"
      >
        {{ cat.name }}
      </button>
    </div>

    <!-- Results -->
    <div v-if="store.loading" class="flex justify-center py-16">
      <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div v-else-if="store.listings.length === 0" class="text-center py-16">
      <div class="text-gray-300 mb-4">
        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <p class="text-gray-500 mb-4">Aucune annonce trouvée</p>
      <button @click="clearFilters" class="btn btn-secondary">Réinitialiser les filtres</button>
    </div>

    <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      <ListingCard
        v-for="listing in store.listings"
        :key="listing.id"
        :listing="listing"
      />
    </div>
  </div>
</template>
