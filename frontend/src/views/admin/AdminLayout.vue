<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 bg-red-100 text-red-700 rounded-lg flex items-center justify-center font-bold">
        A
      </div>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Administration</h1>
        <p class="text-sm text-gray-500">Gestion des utilisateurs et annonces</p>
      </div>
    </div>

    <div class="flex gap-2 border-b border-gray-200 mb-6">
      <RouterLink
        :to="{ name: 'admin-users' }"
        class="px-4 py-2 text-sm font-medium border-b-2 transition"
        active-class="border-primary-600 text-primary-700"
        :class="$route.name === 'admin-users' ? '' : 'border-transparent text-gray-600 hover:text-gray-900'"
      >
        Utilisateurs
      </RouterLink>
      <RouterLink
        :to="{ name: 'admin-listings' }"
        class="px-4 py-2 text-sm font-medium border-b-2 transition"
        active-class="border-primary-600 text-primary-700"
        :class="$route.name === 'admin-listings' ? '' : 'border-transparent text-gray-600 hover:text-gray-900'"
      >
        Annonces
      </RouterLink>
    </div>

    <RouterView />
  </div>
</template>
