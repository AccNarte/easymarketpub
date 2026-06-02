<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import '../styles/produit-view.css'

const produits = ref([])
const loading = ref(true)
const error = ref('')
const submitting = ref(false)
const deletepublic_id = ref('')
const editForm = ref({
  public_id: '',
  nom: '',
  description: '',
  prix: ''
})

const success = ref('')


const newProduit = ref({
  nom: '',
  description: '',
  prix: null
})
const selectedImage = ref(null)

function onImageChange(event) {
  selectedImage.value = event.target.files?.[0] ?? null
}

async function addProduit() {
  submitting.value = true
  error.value = ''

  try {
    const payload = new FormData()
    payload.append('nom', newProduit.value.nom ?? '')
    payload.append('description', newProduit.value.description ?? '')
    payload.append('prix', newProduit.value.prix ?? '')
    if (selectedImage.value) {
      // Pour un fichier, il faut envoyer le binaire via FormData.
      payload.append('image', selectedImage.value)
    }

    await api.post('/produits', payload, {
      // Important: force multipart ici, sinon axios peut garder application/json.
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    const response = await api.get('/produits')
    produits.value = response.data.produits ?? []

    newProduit.value = { nom: '', description: '', prix: null }
    selectedImage.value = null
  } catch (err) {
    const apiErrors = err?.response?.data?.errors
    if (apiErrors) {
      // On affiche la première erreur de validation Laravel.
      const firstError = Object.values(apiErrors)[0]?.[0]
      error.value = firstError || "Impossible d'ajouter le produit."
    } else {
      error.value = "Impossible d'ajouter le produit."
    }
  } finally {
    submitting.value = false
  }
}

async function editProduit() {
  if (!editForm.value.public_id) {
    error.value = "Renseigne un ID produit à modifier."
    return
  }

  submitting.value = true
  error.value = ''

  try {
    const payload = {}
    if (editForm.value.nom) payload.nom = editForm.value.nom
    if (editForm.value.description) payload.description = editForm.value.description
    if (editForm.value.prix !== '' && editForm.value.prix !== null) payload.prix = editForm.value.prix

    await api.put(`/produits/${editForm.value.public_id}`, payload)

    const response = await api.get('/produits')
    produits.value = response.data.produits ?? []
    editForm.value = { public_id: '', nom: '', description: '', prix: '' }
  } catch (err) {
    const apiErrors = err?.response?.data?.errors
    if (apiErrors) {
      const firstError = Object.values(apiErrors)[0]?.[0]
      error.value = firstError || "Impossible de modifier ce produit."
    } else {
      error.value = "Impossible de modifier ce produit."
    }
  } finally {
    submitting.value = false
  }
}
async function deleteProduit() {
  if (!deletepublic_id.value) {
    error.value = "Renseigne un ID produit à supprimer."
    return
  }

  submitting.value = true
  error.value = ''
  success.value = ''

  try {
    const response = await api.delete(`/produits/${deletepublic_id.value}`)
    success.value = response.data.message

    const listResponse = await api.get('/produits')
    produits.value = listResponse.data.produits ?? []

    deletepublic_id.value = ''
  } catch (err) {
    error.value = "Impossible de supprimer ce produit."
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  try {
    const response = await api.get('/produits')
    // Le backend renvoie un objet JSON: { produits: [...] }.
    // On stocke donc le tableau dans produits.value pour l'afficher ensuite dans le template.
    produits.value = response.data.produits ?? []
  } catch (err) {
    error.value = "Impossible de charger les produits."
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="produits-page">
    <h1 class="page-title">Produits</h1>

    <p v-if="loading" class="state-message">Chargement...</p>
    <p v-else-if="error" class="state-message error">{{ error }}</p>

    <ul v-else class="products-grid">
      <!--
        v-for crée une variable locale "produit" pour chaque élément du tableau "produits".
        Exemple d'un élément: { id: 1, nom: "T-shirt", prix: 19.99, ... }
      -->
      <li v-for="produit in produits" :key="produit.public_id" class="product-card">
        <!--
          Ici, "produit.prix" lit la propriété "prix" de l'objet courant dans la boucle.
          Même logique pour "produit.nom", "produit.description", etc.
        -->
        <div class="product-title"><strong>{{ produit.nom }}</strong> {{ produit.prix }} €</div>
        <div class="product-description">{{ produit.description }}</div>
        <img
          v-if="produit.image_url"
          :src="produit.image_url"
          :alt="`Image de ${produit.nom}`"
          class="product-image"
        >
        <small v-else>Pas d'image</small>
      </li>
    </ul>

    <div class="form-section">
      <h2>Ajouter un produit</h2>
      <form @submit.prevent="addProduit" class="product-form">
      <input class="input" type="text" v-model="newProduit.nom" placeholder="Nom du produit">
      <input class="input" type="text" v-model="newProduit.description" placeholder="Description du produit">
      <input class="input" type="number" v-model="newProduit.prix" placeholder="Prix du produit">
      <!-- input file: on utilise @change, pas v-model -->
      <input class="input" type="file" @change="onImageChange">
      <button class="btn btn-primary" type="submit" :disabled="submitting">
        {{ submitting ? 'Ajout...' : 'Ajouter' }}
      </button>
      </form>
    </div>

    <div class="form-section">
      <h2>Delete produit</h2>
      <form @submit.prevent="deleteProduit" class="product-form">
      <select class="input" v-model="deletepublic_id">
        <option disabled value="">Choisir un produit</option>
        <option v-for="produit in produits" :key="produit.public_id" :value="produit.public_id">{{ produit.nom }}</option>
      </select>
      <button class="btn btn-primary" type="submit" :disabled="submitting">Delete</button>

      <p v-if="success" class="state-message success">{{ success }}</p>
      <p v-else-if="error" class="state-message error">{{ error }}</p>
      </form>
    </div>

    <div class="form-section">
        <h2>Edit produit</h2>
        <form @submit.prevent="editProduit" class="product-form">
          <select class="input" v-model="editForm.public_id">
            <option disabled value="">Choisir un produit</option>
            <option
              v-for="produit in produits"
              :key="produit.public_id"
              :value="produit.public_id"
            >
              {{ produit.nom }}
            </option>
          </select>

            <input class="input" type="text" v-model="editForm.nom" placeholder="Nom du produit">
            <input class="input" type="text" v-model="editForm.description" placeholder="Description du produit">
            <input class="input" type="number" v-model="editForm.prix" placeholder="Prix du produit">
            <button class="btn btn-primary" type="submit" :disabled="submitting">Edit</button>

        </form>
    </div>
  </div>
</template>

