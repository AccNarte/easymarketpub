<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirm = ref('')
const loading = ref(false)
const error = ref(null)

const handleSubmit = async () => {
  if (password.value !== passwordConfirm.value) {
    error.value = 'Les mots de passe ne correspondent pas'
    return
  }

  loading.value = true
  error.value = null

  try {
    await auth.register(name.value, email.value, password.value)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Une erreur est survenue'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Créer un compte</h1>
        <p class="text-gray-500 mt-2">Rejoignez la communauté EasyMarket</p>
      </div>

      <div class="card p-6">
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div v-if="error" class="p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            {{ error }}
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
            <input
              v-model="name"
              type="text"
              required
              autocomplete="name"
              class="input"
              placeholder="Jean Dupont"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="email"
              type="email"
              required
              autocomplete="email"
              class="input"
              placeholder="vous@exemple.com"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
            <input
              v-model="password"
              type="password"
              required
              minlength="8"
              autocomplete="new-password"
              class="input"
              placeholder="8 caractères minimum"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer</label>
            <input
              v-model="passwordConfirm"
              type="password"
              required
              autocomplete="new-password"
              class="input"
              placeholder="••••••••"
            />
          </div>

          <button type="submit" :disabled="loading" class="btn btn-primary w-full">
            {{ loading ? 'Création...' : 'Créer mon compte' }}
          </button>
        </form>
      </div>

      <p class="text-center text-sm text-gray-600 mt-6">
        Déjà un compte ?
        <RouterLink to="/connexion" class="text-primary-600 hover:text-primary-700 font-medium">
          Se connecter
        </RouterLink>
      </p>
    </div>
  </div>
</template>
