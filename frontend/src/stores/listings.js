import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useListingsStore = defineStore('listings', () => {
  const listings = ref([])
  const currentListing = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    page: 1,
    perPage: 12,
    total: 0
  })

  async function fetchListings(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/listings', { params })
      listings.value = response.data.data
      pagination.value = {
        page: response.data.current_page,
        perPage: response.data.per_page,
        total: response.data.total
      }
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors du chargement'
    } finally {
      loading.value = false
    }
  }

  async function fetchListing(id) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(`/listings/${id}`)
      currentListing.value = response.data
      return response.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Annonce non trouvée'
      return null
    } finally {
      loading.value = false
    }
  }

  async function createListing(data) {
    const formData = new FormData()
    Object.keys(data).forEach(key => {
      if (key === 'images') {
        data.images.forEach(img => formData.append('images[]', img))
      } else {
        formData.append(key, data[key])
      }
    })

    const response = await api.post('/listings', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return response.data
  }

  async function updateListing(id, data) {
    const response = await api.put(`/listings/${id}`, data)
    return response.data
  }

  async function deleteListing(id) {
    await api.delete(`/listings/${id}`)
    listings.value = listings.value.filter(l => l.id !== id)
  }

  return {
    listings,
    currentListing,
    loading,
    error,
    pagination,
    fetchListings,
    fetchListing,
    createListing,
    updateListing,
    deleteListing
  }
})
