<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard' })

const { getVendorOrders } = useOrders()

const { data: orders } = await useAsyncData('vendor-orders', () => getVendorOrders())

const statutColor = (statut) => ({
  en_attente: 'warning',
  paye: 'success',
  expedie: 'info',
  livre: 'success',
  annule: 'error',
}[statut] || 'neutral')
</script>

<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-6">Commandes sur mes produits</h1>

    <div v-if="orders?.data?.length" class="space-y-3">
      <UCard v-for="order in orders.data" :key="order.id">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium">Commande #{{ order.id }} · {{ order.user?.nom }}</p>
            <p class="text-sm text-gray-500">
              {{ order.items?.map(i => i.product?.nom).join(', ') }}
            </p>
          </div>
          <div class="text-right">
            <p class="font-semibold">{{ Number(order.total).toLocaleString('fr-FR') }} FCFA</p>
            <UBadge :color="statutColor(order.statut)" variant="subtle" size="xs">{{ order.statut }}</UBadge>
          </div>
        </div>
      </UCard>
    </div>
    <p v-else class="text-gray-500">Aucune commande pour le moment.</p>
  </div>
</template>