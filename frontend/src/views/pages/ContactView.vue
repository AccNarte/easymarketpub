<script setup>
import { ref } from 'vue'

const form = ref({ name: '', email: '', subject: '', message: '' })
const sent = ref(false)

const handleSubmit = () => {
  // Simulation d'envoi
  sent.value = true
}
</script>

<template>
  <div class="container-page max-w-xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Contact</h1>

    <div v-if="sent" class="card p-8 text-center">
      <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
        ✓
      </div>
      <h2 class="text-xl font-semibold text-gray-900 mb-2">Message envoyé</h2>
      <p class="text-gray-600">Nous vous répondrons dans les plus brefs délais.</p>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="card p-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
        <input v-model="form.name" type="text" required class="input" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input v-model="form.email" type="email" required class="input" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Sujet</label>
        <select v-model="form.subject" required class="input">
          <option value="">Sélectionner...</option>
          <option value="question">Question générale</option>
          <option value="problem">Signaler un problème</option>
          <option value="partnership">Partenariat</option>
          <option value="other">Autre</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
        <textarea v-model="form.message" rows="5" required class="input"></textarea>
      </div>

      <button type="submit" class="btn btn-primary w-full">Envoyer</button>
    </form>
  </div>
</template>
