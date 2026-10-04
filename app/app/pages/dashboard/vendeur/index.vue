<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard', pageTitle: 'Tableau de bord' })

const { getVendorDashboard } = useOrders()
const { data: stats } = await useAsyncData('vendor-dashboard', () => getVendorDashboard())

const moisLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc']

const barOptions = computed(() => ({
  chart: { toolbar: { show: false }, fontFamily: 'inherit' },
  plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
  dataLabels: { enabled: false },
  colors: ['#1e3a7a'],
  xaxis: { categories: moisLabels, axisBorder: { show: false }, axisTicks: { show: false } },
  grid: { borderColor: '#f1f5f9' },
  yaxis: { labels: { formatter: (v) => `${(v / 1000).toFixed(0)}K` } },
}))

const barSeries = computed(() => [{ name: 'Ventes', data: stats.value?.monthly_sales || [] }])

const statutColor = (statut) => ({
  en_attente: 'warning', paye: 'success', livre: 'success', annule: 'error',
}[statut] || 'neutral')
</script>

<template>
  <div class="p-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <UCard>
        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center mb-3">
          <UIcon name="i-lucide-wallet" class="w-5 h-5 text-primary" />
        </div>
        <p class="text-sm text-gray-500">Chiffre d'affaires</p>
        <p class="text-2xl font-bold mt-1">{{ Number(stats?.total_revenue ?? 0).toLocaleString('fr-FR') }} FCFA</p>
      </UCard>
      <UCard>
        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center mb-3">
          <UIcon name="i-lucide-package" class="w-5 h-5 text-primary" />
        </div>
        <p class="text-sm text-gray-500">Produits actifs</p>
        <p class="text-2xl font-bold mt-1">{{ stats?.total_products ?? 0 }}</p>
      </UCard>
      <UCard>
        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center mb-3">
          <UIcon name="i-lucide-shopping-cart" class="w-5 h-5 text-primary" />
        </div>
        <p class="text-sm text-gray-500">Commandes</p>
        <p class="text-2xl font-bold mt-1">{{ stats?.total_orders ?? 0 }}</p>
      </UCard>
    </div>

    <UCard class="mb-6">
      <template #header>
        <h2 class="font-semibold">Ventes mensuelles</h2>
      </template>
      <ClientOnly>
        <apexchart type="bar" height="280" :options="barOptions" :series="barSeries" />
      </ClientOnly>
    </UCard>

    <UCard>
      <template #header>
        <h2 class="font-semibold">Commandes récentes</h2>
      </template>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500 border-b border-gray-200 dark:border-gray-800">
            <th class="pb-2">Client</th>
            <th class="pb-2">Articles</th>
            <th class="pb-2">Total</th>
            <th class="pb-2">Statut</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in stats?.recent_orders" :key="order.id" class="border-b border-gray-100 dark:border-gray-800 last:border-0">
            <td class="py-3">{{ order.user?.nom }}</td>
            <td class="py-3 text-gray-500">{{ order.items?.map(i => i.product?.nom).join(', ') }}</td>
            <td class="py-3">{{ Number(order.total).toLocaleString('fr-FR') }} FCFA</td>
            <td class="py-3"><UBadge :color="statutColor(order.statut)" variant="subtle" size="xs">{{ order.statut }}</UBadge></td>
          </tr>
        </tbody>
      </table>
      <p v-if="!stats?.recent_orders?.length" class="text-gray-500 text-sm py-4 text-center">Aucune commande pour le moment.</p>
    </UCard>
  </div>
</template>