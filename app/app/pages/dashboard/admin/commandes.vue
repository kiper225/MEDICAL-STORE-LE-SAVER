<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard' })

const { getAllOrders, updateOrderStatus } = useOrders()
const toast = useToast()

const { data: orders, refresh } = await useAsyncData('admin-orders', () => getAllOrders())

const statutColor = (statut) => ({
  en_attente: 'warning',
  paye: 'success',
  expedie: 'info',
  livre: 'success',
  annule: 'error',
}[statut] || 'neutral')

const statutOptions = [
  { label: 'En attente', value: 'en_attente' },
  { label: 'Payé', value: 'paye' },
  { label: 'Expédié', value: 'expedie' },
  { label: 'Livré', value: 'livre' },
  { label: 'Annulé', value: 'annule' },
]

const handleStatusChange = async (orderId, statut) => {
  try {
    await updateOrderStatus(orderId, statut)
    toast.add({ title: 'Statut mis à jour', color: 'success' })
    refresh()
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  }
}
</script>

<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-6">Commandes</h1>

    <UCard>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500 border-b">
            <th class="pb-2">Client</th>
            <th class="pb-2">Articles</th>
            <th class="pb-2">Total</th>
            <th class="pb-2">Paiement</th>
            <th class="pb-2">Statut</th>
            <th class="pb-2">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders?.data" :key="order.id" class="border-b last:border-0">
            <td class="py-3">{{ order.user?.nom }}</td>
            <td class="py-3 text-gray-500">
              {{ order.items?.map(i => i.product?.nom).join(', ') }}
            </td>
            <td class="py-3">{{ Number(order.total).toLocaleString('fr-FR') }} FCFA</td>
            <td class="py-3 text-gray-500 uppercase text-xs">{{ order.mode_paiement }}</td>
            <td class="py-3">
              <USelect
                :model-value="order.statut"
                :items="statutOptions"
                size="xs"
                class="w-36"
                @update:model-value="(val) => handleStatusChange(order.id, val)"
              />
            </td>
            <td class="py-3 text-gray-500">{{ new Date(order.created_at).toLocaleDateString('fr-FR') }}</td>
          </tr>
        </tbody>
      </table>
    </UCard>
  </div>
</template>