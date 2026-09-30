<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard', pageTitle: 'Mon espace' })

const { getDashboard } = useAdmin()
const { data: stats } = await useAsyncData('admin-dashboard', () => getDashboard())

const statutColor = (statut) => ({
  en_attente: 'warning',
  paye: 'success',
  annule: 'error',
}[statut] || 'neutral')
</script>

<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-6">Tableau de bord</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <UCard>
        <p class="text-sm text-gray-500">Utilisateurs</p>
        <p class="text-3xl font-bold mt-1">{{ stats?.total_users ?? 0 }}</p>
      </UCard>
      <UCard>
        <p class="text-sm text-gray-500">Produits actifs</p>
        <p class="text-3xl font-bold mt-1">{{ stats?.total_products ?? 0 }}</p>
      </UCard>
      <UCard>
        <p class="text-sm text-gray-500">Commandes</p>
        <p class="text-3xl font-bold mt-1">{{ stats?.total_orders ?? 0 }}</p>
      </UCard>
      <UCard>
        <p class="text-sm text-gray-500">Installations en attente</p>
        <p class="text-3xl font-bold mt-1 text-warning">{{ stats?.installations_en_attente ?? 0 }}</p>
      </UCard>
    </div>

    <UCard>
      <template #header>
        <h2 class="font-semibold">Commandes récentes</h2>
      </template>

      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500 border-b">
            <th class="pb-2">Client</th>
            <th class="pb-2">Total</th>
            <th class="pb-2">Statut</th>
            <th class="pb-2">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in stats?.recent_orders" :key="order.id" class="border-b last:border-0">
            <td class="py-3">{{ order.user?.nom }}</td>
            <td class="py-3">{{ Number(order.total).toLocaleString('fr-FR') }} FCFA</td>
            <td class="py-3">
              <UBadge :color="statutColor(order.statut)" variant="subtle" size="xs">{{ order.statut }}</UBadge>
            </td>
            <td class="py-3 text-gray-500">{{ new Date(order.created_at).toLocaleDateString('fr-FR') }}</td>
          </tr>
        </tbody>
      </table>
    </UCard>
  </div>
</template>