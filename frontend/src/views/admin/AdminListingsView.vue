<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../services/api'

const listings = ref([])
const loading = ref(false)
const search = ref('')
const filterStatus = ref('')
const page = ref(1)
const lastPage = ref(1)

const selected = ref(null)
const editForm = ref({ title: '', description: '', price: 0, status: 'active' })
const errors = ref({})
const flash = ref(null)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/listings', {
      params: {
        q: search.value || undefined,
        status: filterStatus.value || undefined,
        page: page.value,
      }
    })
    listings.value = data.data
    lastPage.value = data.last_page
  } finally {
    loading.value = false
  }
}

function openEdit(l) {
  selected.value = l
  editForm.value = {
    title: l.title,
    description: l.description,
    price: l.price,
    status: l.status,
  }
  errors.value = {}
}

function close() { selected.value = null }

function showFlash(message) {
  flash.value = message
  setTimeout(() => (flash.value = null), 3000)
}

async function save() {
  errors.value = {}
  try {
    await api.put(`/admin/listings/${selected.value.id}`, editForm.value)
    showFlash('Annonce mise à jour')
    await load()
    close()
  } catch (e) {
    errors.value = e.response?.data?.errors || {}
  }
}

async function destroy(l) {
  if (!confirm(`Supprimer définitivement l'annonce "${l.title}" ?`)) return
  try {
    await api.delete(`/admin/listings/${l.id}`)
    showFlash('Annonce supprimée')
    await load()
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur')
  }
}

const statusLabel = (s) => ({ active: 'Active', sold: 'Vendue', inactive: 'Inactive' }[s] || s)
const statusClass = (s) => ({
  active: 'bg-green-100 text-green-700',
  sold: 'bg-blue-100 text-blue-700',
  inactive: 'bg-gray-100 text-gray-700',
}[s] || 'bg-gray-100 text-gray-700')

watch([search, filterStatus], () => { page.value = 1; load() })
onMounted(load)
</script>

<template>
  <div>
    <div v-if="flash" class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded-lg text-sm">
      {{ flash }}
    </div>

    <div class="bg-white p-4 rounded-lg border border-gray-200 mb-4 grid sm:grid-cols-3 gap-3">
      <input
        v-model="search"
        type="text"
        placeholder="Rechercher (titre, description)..."
        class="px-3 py-2 border border-gray-300 rounded-lg sm:col-span-2"
      />
      <select v-model="filterStatus" class="px-3 py-2 border border-gray-300 rounded-lg">
        <option value="">Tous les statuts</option>
        <option value="active">Actives</option>
        <option value="sold">Vendues</option>
        <option value="inactive">Inactives</option>
      </select>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="px-4 py-3 font-medium text-gray-700">Titre</th>
            <th class="px-4 py-3 font-medium text-gray-700">Vendeur</th>
            <th class="px-4 py-3 font-medium text-gray-700">Prix</th>
            <th class="px-4 py-3 font-medium text-gray-700">Catégorie</th>
            <th class="px-4 py-3 font-medium text-gray-700">Statut</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Chargement...</td>
          </tr>
          <tr v-else-if="!listings.length">
            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Aucune annonce</td>
          </tr>
          <tr v-for="l in listings" :key="l.id" class="border-t border-gray-100 hover:bg-gray-50">
            <td class="px-4 py-3">
              <p class="font-medium text-gray-900 line-clamp-1">{{ l.title }}</p>
              <p class="text-xs text-gray-500 line-clamp-1">{{ l.description }}</p>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ l.user?.name }}</td>
            <td class="px-4 py-3 text-gray-700">{{ l.price }} €</td>
            <td class="px-4 py-3 text-gray-600">{{ l.category?.name || '-' }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 text-xs rounded-full" :class="statusClass(l.status)">
                {{ statusLabel(l.status) }}
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <button @click="openEdit(l)" class="text-primary-600 hover:underline mr-3">
                Modifier
              </button>
              <button @click="destroy(l)" class="text-red-600 hover:underline">
                Suppr.
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="lastPage > 1" class="flex justify-center gap-2 mt-4">
      <button
        v-for="p in lastPage"
        :key="p"
        @click="page = p; load()"
        :class="p === page ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 border border-gray-300'"
        class="px-3 py-1 rounded-lg text-sm"
      >
        {{ p }}
      </button>
    </div>

    <!-- Modal -->
    <div
      v-if="selected"
      class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
      @click.self="close"
    >
      <div class="bg-white rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">Modifier l'annonce</h2>
          <button @click="close" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="p-6 space-y-3">
          <label class="block">
            <span class="text-xs text-gray-600">Titre</span>
            <input v-model="editForm.title" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
            <span v-if="errors.title" class="text-xs text-red-600">{{ errors.title[0] }}</span>
          </label>
          <label class="block">
            <span class="text-xs text-gray-600">Description</span>
            <textarea v-model="editForm.description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
          </label>
          <div class="grid sm:grid-cols-2 gap-3">
            <label class="block">
              <span class="text-xs text-gray-600">Prix (€)</span>
              <input v-model.number="editForm.price" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
            </label>
            <label class="block">
              <span class="text-xs text-gray-600">Statut</span>
              <select v-model="editForm.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="active">Active</option>
                <option value="sold">Vendue</option>
                <option value="inactive">Inactive</option>
              </select>
            </label>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
            <button @click="close" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg text-sm">
              Annuler
            </button>
            <button @click="save" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm hover:bg-primary-700">
              Enregistrer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
