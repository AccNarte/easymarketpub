<script setup>
import { ref, onMounted, computed } from 'vue'
import { useWalletStore } from '../stores/wallet'

const wallet = useWalletStore()

const activeTab = ref('transactions')
const modal = ref(null) // 'deposit', 'withdraw', 'add-method'
const amount = ref('')
const selectedMethodId = ref(null)
const newMethod = ref({ type: 'card', provider: '', label: '' })

const loading = ref(false)
const message = ref({ type: '', text: '' })

const formatPrice = (n) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(n)
const formatDate = (d) => new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })

const providers = computed(() => wallet.providers[newMethod.value.type] || [])

const showMessage = (type, text) => {
  message.value = { type, text }
  setTimeout(() => message.value = { type: '', text: '' }, 3000)
}

const handleDeposit = async () => {
  if (!amount.value || !selectedMethodId.value) return
  loading.value = true
  try {
    await wallet.deposit(parseFloat(amount.value), selectedMethodId.value)
    showMessage('success', 'Rechargement effectué')
    modal.value = null
    amount.value = ''
    wallet.fetchTransactions()
  } catch (e) {
    showMessage('error', e.response?.data?.message || 'Erreur')
  } finally {
    loading.value = false
  }
}

const handleWithdraw = async () => {
  if (!amount.value || !selectedMethodId.value) return
  loading.value = true
  try {
    await wallet.withdraw(parseFloat(amount.value), selectedMethodId.value)
    showMessage('success', 'Retrait effectué')
    modal.value = null
    amount.value = ''
    wallet.fetchTransactions()
  } catch (e) {
    showMessage('error', e.response?.data?.message || 'Erreur')
  } finally {
    loading.value = false
  }
}

const handleAddMethod = async () => {
  if (!newMethod.value.provider || !newMethod.value.label) return
  loading.value = true
  try {
    await wallet.addPaymentMethod(newMethod.value)
    showMessage('success', 'Moyen de paiement ajouté')
    modal.value = null
    newMethod.value = { type: 'card', provider: '', label: '' }
  } catch (e) {
    showMessage('error', e.response?.data?.message || 'Erreur')
  } finally {
    loading.value = false
  }
}

const deleteMethod = async (id) => {
  if (!confirm('Supprimer ce moyen de paiement ?')) return
  try {
    await wallet.removePaymentMethod(id)
    showMessage('success', 'Supprimé')
  } catch (e) {
    showMessage('error', 'Impossible de supprimer')
  }
}

onMounted(async () => {
  await Promise.all([
    wallet.fetchBalance(),
    wallet.fetchTransactions(),
    wallet.fetchPaymentMethods(),
    wallet.fetchProviders()
  ])
  if (wallet.paymentMethods.length) {
    selectedMethodId.value = wallet.paymentMethods.find(m => m.is_default)?.id || wallet.paymentMethods[0].id
  }
})
</script>

<template>
  <div class="container-page max-w-2xl">
    <!-- Message -->
    <div v-if="message.text" :class="['mb-4 p-3 rounded-lg text-sm', message.type === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700']">
      {{ message.text }}
    </div>

    <!-- Balance -->
    <div class="card p-6 mb-6">
      <p class="text-sm text-gray-500 mb-1">Mon solde</p>
      <p class="text-3xl font-bold text-gray-900 mb-4">{{ formatPrice(wallet.balance) }}</p>
      <div class="flex gap-3">
        <button @click="modal = 'deposit'" class="btn btn-primary flex-1">
          + Recharger
        </button>
        <button @click="modal = 'withdraw'" class="btn btn-secondary flex-1">
          Retirer
        </button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-4 mb-4 border-b border-gray-200">
      <button @click="activeTab = 'transactions'" :class="['pb-3 text-sm font-medium border-b-2 -mb-px', activeTab === 'transactions' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500']">
        Historique
      </button>
      <button @click="activeTab = 'methods'" :class="['pb-3 text-sm font-medium border-b-2 -mb-px', activeTab === 'methods' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500']">
        Moyens de paiement
      </button>
    </div>

    <!-- Transactions -->
    <div v-if="activeTab === 'transactions'">
      <div v-if="wallet.loading" class="text-center py-8">
        <div class="w-6 h-6 border-2 border-primary-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
      </div>
      <div v-else-if="!wallet.transactions.length" class="text-center py-8 text-gray-500">
        Aucune transaction
      </div>
      <div v-else class="space-y-2">
        <div v-for="tx in wallet.transactions" :key="tx.id" class="card p-4 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm', ['deposit','sale','refund'].includes(tx.type) ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600']">
              {{ ['deposit','sale','refund'].includes(tx.type) ? '↓' : '↑' }}
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">{{ tx.description }}</p>
              <p class="text-xs text-gray-500">{{ formatDate(tx.created_at) }}</p>
            </div>
          </div>
          <p :class="['font-semibold', ['deposit','sale','refund'].includes(tx.type) ? 'text-green-600' : 'text-red-600']">
            {{ ['deposit','sale','refund'].includes(tx.type) ? '+' : '-' }}{{ formatPrice(tx.amount) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Payment Methods -->
    <div v-if="activeTab === 'methods'">
      <button @click="modal = 'add-method'" class="btn btn-secondary w-full mb-4">
        + Ajouter un moyen de paiement
      </button>

      <div v-if="!wallet.paymentMethods.length" class="text-center py-8 text-gray-500">
        Aucun moyen de paiement
      </div>
      <div v-else class="space-y-2">
        <div v-for="m in wallet.paymentMethods" :key="m.id" class="card p-4 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
              {{ m.type === 'crypto' ? '₿' : m.type === 'card' ? '💳' : '🏦' }}
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">{{ m.label }}</p>
              <p class="text-xs text-gray-500 capitalize">{{ m.provider }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <span v-if="m.is_default" class="badge-primary">Défaut</span>
            <button v-else @click="wallet.setDefaultPaymentMethod(m.id)" class="text-xs text-gray-500 hover:text-primary-600">
              Définir défaut
            </button>
            <button @click="deleteMethod(m.id)" class="p-1 text-gray-400 hover:text-red-500">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Backdrop -->
    <div v-if="modal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="modal = null">
      <!-- Deposit Modal -->
      <div v-if="modal === 'deposit'" class="bg-white rounded-xl p-6 w-full max-w-sm">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recharger</h3>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Montant (€)</label>
            <input v-model="amount" type="number" min="1" class="input" placeholder="50" />
          </div>

          <div class="flex gap-2">
            <button v-for="a in [20, 50, 100, 200]" :key="a" @click="amount = a"
              :class="['flex-1 py-2 rounded-lg border text-sm', amount == a ? 'border-primary-600 bg-primary-50 text-primary-700' : 'border-gray-200']">
              {{ a }}€
            </button>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Via</label>
            <select v-model="selectedMethodId" class="input">
              <option v-for="m in wallet.paymentMethods" :key="m.id" :value="m.id">{{ m.label }}</option>
            </select>
          </div>

          <div v-if="!wallet.paymentMethods.length" class="p-3 bg-amber-50 text-amber-700 text-sm rounded-lg">
            Ajoutez d'abord un moyen de paiement
          </div>
        </div>

        <div class="flex gap-2 mt-6">
          <button @click="modal = null" class="btn btn-secondary flex-1">Annuler</button>
          <button @click="handleDeposit" :disabled="loading || !wallet.paymentMethods.length" class="btn btn-primary flex-1">
            {{ loading ? '...' : 'Recharger' }}
          </button>
        </div>
      </div>

      <!-- Withdraw Modal -->
      <div v-if="modal === 'withdraw'" class="bg-white rounded-xl p-6 w-full max-w-sm">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Retirer</h3>
        <p class="text-sm text-gray-500 mb-4">Disponible: {{ formatPrice(wallet.balance) }}</p>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Montant (€)</label>
            <input v-model="amount" type="number" min="10" :max="wallet.balance" class="input" placeholder="50" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Vers</label>
            <select v-model="selectedMethodId" class="input">
              <option v-for="m in wallet.paymentMethods" :key="m.id" :value="m.id">{{ m.label }}</option>
            </select>
          </div>
        </div>

        <div class="flex gap-2 mt-6">
          <button @click="modal = null" class="btn btn-secondary flex-1">Annuler</button>
          <button @click="handleWithdraw" :disabled="loading || amount > wallet.balance" class="btn btn-primary flex-1">
            {{ loading ? '...' : 'Retirer' }}
          </button>
        </div>
      </div>

      <!-- Add Method Modal -->
      <div v-if="modal === 'add-method'" class="bg-white rounded-xl p-6 w-full max-w-sm">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ajouter un moyen de paiement</h3>

        <div class="space-y-4">
          <div class="flex gap-2">
            <button @click="newMethod.type = 'card'; newMethod.provider = ''"
              :class="['flex-1 py-3 rounded-lg border-2 text-sm', newMethod.type === 'card' ? 'border-primary-600 bg-primary-50' : 'border-gray-200']">
              💳 Carte
            </button>
            <button @click="newMethod.type = 'bank'; newMethod.provider = ''"
              :class="['flex-1 py-3 rounded-lg border-2 text-sm', newMethod.type === 'bank' ? 'border-primary-600 bg-primary-50' : 'border-gray-200']">
              🏦 Banque
            </button>
            <button @click="newMethod.type = 'crypto'; newMethod.provider = ''"
              :class="['flex-1 py-3 rounded-lg border-2 text-sm', newMethod.type === 'crypto' ? 'border-primary-600 bg-primary-50' : 'border-gray-200']">
              ₿ Crypto
            </button>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur</label>
            <select v-model="newMethod.provider" class="input">
              <option value="">Sélectionner...</option>
              <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
            <input v-model="newMethod.label" type="text" class="input" placeholder="Ma carte principale" />
          </div>
        </div>

        <div class="flex gap-2 mt-6">
          <button @click="modal = null" class="btn btn-secondary flex-1">Annuler</button>
          <button @click="handleAddMethod" :disabled="loading || !newMethod.provider || !newMethod.label" class="btn btn-primary flex-1">
            {{ loading ? '...' : 'Ajouter' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
