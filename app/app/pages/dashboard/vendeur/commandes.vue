<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard', pageTitle: 'Mes commandes' })

const authStore = useAuthStore()
const { getVendorOrders, updateItemStatus } = useOrders()
const toast = useToast()

const { data: orders, refresh } = await useAsyncData('vendor-orders', () => getVendorOrders())

const myItems = (order) =>
  order.items?.filter((i) => i.product?.vendor_id === authStore.user.id) || []

const mySubtotal = (order) =>
  myItems(order).reduce((sum, i) => sum + Number(i.prix_unitaire) * i.quantite, 0)

const statutOptions = [
  { label: 'En préparation', value: 'en_preparation' },
  { label: 'Expédiée', value: 'expedie' },
  { label: 'Livrée', value: 'livre' },
  { label: 'Annulée', value: 'annule' },
]

const statutColor = (statut) => ({
  en_preparation: 'warning',
  expedie: 'info',
  livre: 'success',
  annule: 'error',
}[statut] || 'neutral')

const handleStatusChange = async (itemId, statut) => {
  try {
    await updateItemStatus(itemId, statut)
    toast.add({ title: 'Statut mis à jour', color: 'success' })
    refresh()
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  }
}
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Commandes sur mes produits</h1>

    <div v-if="orders?.data?.length" class="space-y-4">
      <UCard v-for="order in orders.data" :key="order.id">
        <template #header>
          <div class="flex items-center justify-between">
            <span class="font-medium">Commande #{{ order.id }} — {{ order.user?.nom }}</span>
            <span class="text-sm text-gray-500">{{ new Date(order.created_at).toLocaleDateString('fr-FR') }}</span>
          </div>
        </template>

        <div class="space-y-3">
          <div
            v-for="item in myItems(order)"
            :key="item.id"
            class="flex items-center justify-between gap-4 py-2 border-b border-gray-100 dark:border-gray-800 last:border-0"
          >
            <div class="flex-1">
              <p class="text-sm font-medium">{{ item.product?.nom }}</p>
              <p class="text-xs text-gray-500">
                {{ item.quantite }} × {{ Number(item.prix_unitaire).toLocaleString('fr-FR') }} FCFA
              </p>
            </div>
            <USelect
              :model-value="item.statut"
              :items="statutOptions"
              size="xs"
              class="w-40"
              @update:model-value="(val) => handleStatusChange(item.id, val)"
            />
          </div>
        </div>

        <template #footer>
          <div class="flex justify-between items-center text-sm">
            <span class="text-gray-500">Votre part sur cette commande</span>
            <span class="font-bold text-primary">{{ mySubtotal(order).toLocaleString('fr-FR') }} FCFA</span>
          </div>
        </template>
      </UCard>
    </div>
    <p v-else class="text-gray-500">Aucune commande pour le moment.</p>
  </div>
</template>