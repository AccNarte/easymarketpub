<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useListingsStore } from '../stores/listings'
import ListingCard from '../components/ListingCard.vue'

const store = useListingsStore()
const router = useRouter()
const searchQuery = ref('')

const categories = [
  { name: 'Véhicules', slug: 'vehicules', icon: '🚗' },
  { name: 'Immobilier', slug: 'immobilier', icon: '🏠' },
  { name: 'Électronique', slug: 'electronique', icon: '📱' },
  { name: 'Mode', slug: 'mode', icon: '👕' },
  { name: 'Maison', slug: 'maison', icon: '🛋️' },
  { name: 'Loisirs', slug: 'loisirs', icon: '⚽' },
]

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ name: 'listings', query: { q: searchQuery.value } })
  }
}

onMounted(() => {
  store.fetchListings({ limit: 8 })
})
</script>

<template>
  <div>
    <!-- Banniere -->
    <section class="bg-white border-b border-gray-200">
      <div class="max-w-4xl mx-auto px-4 py-16 text-center">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
          Achetez et vendez simplement
        </h1>
        <p class="text-lg text-gray-600 mb-8">
          Des milliers d'annonces près de chez vous
        </p>

        <!-- Recherche -->
        <form @submit.prevent="handleSearch" class="max-w-xl mx-auto flex gap-2">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Que recherchez-vous ?"
            class="input flex-1"
          />
          <button type="submit" class="btn btn-primary px-6">
            Rechercher
          </button>
        </form>
      </div>
    </section>

    <!-- Categories -->
    <section class="container-page">
      <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
        <RouterLink
          v-for="cat in categories"
          :key="cat.slug"
          :to="{ name: 'listings', query: { category: cat.slug } }"
          class="card p-4 text-center hover:border-primary-300 hover:bg-primary-50 transition"
        >
          <span class="text-2xl mb-2 block">{{ cat.icon }}</span>
          <span class="text-sm font-medium text-gray-700">{{ cat.name }}</span>
        </RouterLink>
      </div>
    </section>

    <!-- Annonces recentes -->
    <section class="container-page">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Annonces récentes</h2>
        <RouterLink to="/annonces" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
          Voir tout →
        </RouterLink>
      </div>

      <div v-if="store.loading" class="flex justify-center py-12">
        <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <div v-else-if="store.listings.length === 0" class="text-center py-12 text-gray-500">
        Aucune annonce pour le moment
      </div>

      <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <ListingCard
          v-for="listing in store.listings"
          :key="listing.id"
          :listing="listing"
        />
      </div>
    </section>

    <!-- CTA -->
    <section class="container-page">
      <div class="bg-primary-600 rounded-2xl p-8 sm:p-12 text-center text-white">
        <h2 class="text-2xl font-bold mb-4">Vous avez quelque chose à vendre ?</h2>
        <p class="text-primary-100 mb-6">Déposez votre annonce gratuitement en quelques clics</p>
        <RouterLink to="/publier" class="inline-flex items-center gap-2 bg-white text-primary-700 px-6 py-3 rounded-lg font-medium hover:bg-primary-50 transition">
          Déposer une annonce
        </RouterLink>
      </div>
    </section>
  </div>
</template>
