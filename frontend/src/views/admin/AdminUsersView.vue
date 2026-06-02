<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../services/api'

const users = ref([])
const loading = ref(false)
const search = ref('')
const filterRole = ref('')
const bannedOnly = ref(false)
const page = ref(1)
const lastPage = ref(1)

const selected = ref(null)
const editForm = ref({ name: '', email: '', role: 'user', balance: 0 })
const passwordForm = ref({ password: '', password_confirmation: '' })
const banForm = ref({ reason: '' })
const errors = ref({})
const flash = ref(null)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/users', {
      params: {
        q: search.value || undefined,
        role: filterRole.value || undefined,
        banned_only: bannedOnly.value ? 1 : undefined,
        page: page.value,
      }
    })
    users.value = data.data
    lastPage.value = data.last_page
  } finally {
    loading.value = false
  }
}

function openEdit(u) {
  selected.value = u
  editForm.value = { name: u.name, email: u.email, role: u.role, balance: u.balance }
  passwordForm.value = { password: '', password_confirmation: '' }
  banForm.value = { reason: u.banned_reason || '' }
  errors.value = {}
}

function close() {
  selected.value = null
}

function showFlash(message) {
  flash.value = message
  setTimeout(() => (flash.value = null), 3000)
}

async function saveProfile() {
  errors.value = {}
  try {
    await api.put(`/admin/users/${selected.value.id}`, editForm.value)
    showFlash('Profil mis à jour')
    await load()
    close()
  } catch (e) {
    errors.value = e.response?.data?.errors || {}
  }
}

async function changePassword() {
  errors.value = {}
  try {
    await api.put(`/admin/users/${selected.value.id}/password`, passwordForm.value)
    showFlash('Mot de passe réinitialisé')
    passwordForm.value = { password: '', password_confirmation: '' }
  } catch (e) {
    errors.value = e.response?.data?.errors || {}
  }
}

async function ban() {
  try {
    await api.post(`/admin/users/${selected.value.id}/ban`, banForm.value)
    showFlash('Utilisateur banni')
    await load()
    close()
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur')
  }
}

async function unban() {
  try {
    await api.post(`/admin/users/${selected.value.id}/unban`)
    showFlash('Bannissement levé')
    await load()
    close()
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur')
  }
}

async function destroy(u) {
  if (!confirm(`Supprimer définitivement ${u.name} ?`)) return
  try {
    await api.delete(`/admin/users/${u.id}`)
    showFlash('Utilisateur supprimé')
    await load()
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur')
  }
}

watch([search, filterRole, bannedOnly], () => {
  page.value = 1
  load()
})

onMounted(load)
</script>

<template>
  <div>
    <!-- Message flash -->
    <div v-if="flash" class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded-lg text-sm">
      {{ flash }}
    </div>

    <!-- Filtres -->
    <div class="bg-white p-4 rounded-lg border border-gray-200 mb-4 grid sm:grid-cols-4 gap-3">
      <input
        v-model="search"
        type="text"
        placeholder="Rechercher (nom ou email)..."
        class="px-3 py-2 border border-gray-300 rounded-lg sm:col-span-2"
      />
      <select v-model="filterRole" class="px-3 py-2 border border-gray-300 rounded-lg">
        <option value="">Tous les rôles</option>
        <option value="user">Utilisateurs</option>
        <option value="admin">Administrateurs</option>
      </select>
      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" v-model="bannedOnly" />
        Bannis uniquement
      </label>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="px-4 py-3 font-medium text-gray-700">Utilisateur</th>
            <th class="px-4 py-3 font-medium text-gray-700">Email</th>
            <th class="px-4 py-3 font-medium text-gray-700">Rôle</th>
            <th class="px-4 py-3 font-medium text-gray-700">Solde</th>
            <th class="px-4 py-3 font-medium text-gray-700">Annonces</th>
            <th class="px-4 py-3 font-medium text-gray-700">Statut</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Chargement...</td>
          </tr>
          <tr v-else-if="!users.length">
            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucun utilisateur</td>
          </tr>
          <tr v-for="u in users" :key="u.id" class="border-t border-gray-100 hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center font-medium">
                  {{ u.name[0]?.toUpperCase() }}
                </div>
                <span class="font-medium text-gray-900">{{ u.name }}</span>
              </div>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ u.email }}</td>
            <td class="px-4 py-3">
              <span
                class="px-2 py-0.5 text-xs rounded-full"
                :class="u.role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'"
              >
                {{ u.role }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-700">{{ u.balance }} €</td>
            <td class="px-4 py-3 text-gray-700">{{ u.listings_count }}</td>
            <td class="px-4 py-3">
              <span v-if="u.banned_at" class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700">
                Banni
              </span>
              <span v-else class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">
                Actif
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <button @click="openEdit(u)" class="text-primary-600 hover:underline mr-3">
                Gérer
              </button>
              <button @click="destroy(u)" class="text-red-600 hover:underline">
                Suppr.
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
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
          <h2 class="text-lg font-bold text-gray-900">Gérer {{ selected.name }}</h2>
          <button @click="close" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="p-6 space-y-6">
          <!-- Profil -->
          <section>
            <h3 class="font-semibold text-gray-900 mb-3">Profil</h3>
            <div class="grid sm:grid-cols-2 gap-3">
              <label class="block">
                <span class="text-xs text-gray-600">Nom</span>
                <input v-model="editForm.name" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
                <span v-if="errors.name" class="text-xs text-red-600">{{ errors.name[0] }}</span>
              </label>
              <label class="block">
                <span class="text-xs text-gray-600">Email</span>
                <input v-model="editForm.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
                <span v-if="errors.email" class="text-xs text-red-600">{{ errors.email[0] }}</span>
              </label>
              <label class="block">
                <span class="text-xs text-gray-600">Rôle</span>
                <select v-model="editForm.role" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                  <option value="user">user</option>
                  <option value="admin">admin</option>
                </select>
              </label>
              <label class="block">
                <span class="text-xs text-gray-600">Solde (€)</span>
                <input v-model.number="editForm.balance" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
              </label>
            </div>
            <button @click="saveProfile" class="mt-3 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm hover:bg-primary-700">
              Enregistrer
            </button>
          </section>

          <!-- Mot de passe -->
          <section class="border-t border-gray-100 pt-4">
            <h3 class="font-semibold text-gray-900 mb-3">Réinitialiser le mot de passe</h3>
            <div class="grid sm:grid-cols-2 gap-3">
              <input v-model="passwordForm.password" type="password" placeholder="Nouveau mot de passe" class="px-3 py-2 border border-gray-300 rounded-lg" />
              <input v-model="passwordForm.password_confirmation" type="password" placeholder="Confirmer" class="px-3 py-2 border border-gray-300 rounded-lg" />
            </div>
            <span v-if="errors.password" class="text-xs text-red-600 block mt-1">{{ errors.password[0] }}</span>
            <button @click="changePassword" class="mt-3 px-4 py-2 bg-amber-600 text-white rounded-lg text-sm hover:bg-amber-700">
              Changer le mot de passe
            </button>
            <p class="text-xs text-gray-500 mt-1">Déconnecte l'utilisateur de toutes ses sessions.</p>
          </section>

          <!-- Bannissement -->
          <section class="border-t border-gray-100 pt-4">
            <h3 class="font-semibold text-gray-900 mb-3">Bannissement</h3>
            <template v-if="selected.banned_at">
              <p class="text-sm text-red-700 mb-2">
                Banni le {{ new Date(selected.banned_at).toLocaleDateString('fr-FR') }}
                <span v-if="selected.banned_reason"> — {{ selected.banned_reason }}</span>
              </p>
              <button @click="unban" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                Lever le bannissement
              </button>
            </template>
            <template v-else>
              <textarea
                v-model="banForm.reason"
                rows="2"
                placeholder="Raison (optionnel)"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
              ></textarea>
              <button @click="ban" class="mt-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                Bannir cet utilisateur
              </button>
            </template>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>
