import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useWalletStore = defineStore('wallet', () => {
  const balance = ref(0)
  const transactions = ref([])
  const paymentMethods = ref([])
  const providers = ref({})
  const loading = ref(false)

  async function fetchBalance() {
    const response = await api.get('/wallet/balance')
    balance.value = parseFloat(response.data.balance)
    return balance.value
  }

  async function fetchTransactions() {
    loading.value = true
    try {
      const response = await api.get('/wallet/transactions')
      transactions.value = response.data.data
      return transactions.value
    } finally {
      loading.value = false
    }
  }

  async function fetchPaymentMethods() {
    const response = await api.get('/payment-methods')
    paymentMethods.value = response.data
    return paymentMethods.value
  }

  async function fetchProviders() {
    const response = await api.get('/wallet/providers')
    providers.value = response.data
    return providers.value
  }

  async function deposit(amount, paymentMethodId) {
    const response = await api.post('/wallet/deposit', {
      amount,
      payment_method_id: paymentMethodId
    })
    balance.value = parseFloat(response.data.new_balance)
    return response.data
  }

  async function withdraw(amount, paymentMethodId) {
    const response = await api.post('/wallet/withdraw', {
      amount,
      payment_method_id: paymentMethodId
    })
    balance.value = parseFloat(response.data.new_balance)
    return response.data
  }

  async function addPaymentMethod(data) {
    const response = await api.post('/payment-methods', data)
    paymentMethods.value.push(response.data)
    return response.data
  }

  async function removePaymentMethod(id) {
    await api.delete(`/payment-methods/${id}`)
    paymentMethods.value = paymentMethods.value.filter(m => m.id !== id)
  }

  async function setDefaultPaymentMethod(id) {
    const response = await api.post(`/payment-methods/${id}/default`)
    paymentMethods.value = paymentMethods.value.map(m => ({
      ...m,
      is_default: m.id === id
    }))
    return response.data
  }

  return {
    balance,
    transactions,
    paymentMethods,
    providers,
    loading,
    fetchBalance,
    fetchTransactions,
    fetchPaymentMethods,
    fetchProviders,
    deposit,
    withdraw,
    addPaymentMethod,
    removePaymentMethod,
    setDefaultPaymentMethod
  }
})
