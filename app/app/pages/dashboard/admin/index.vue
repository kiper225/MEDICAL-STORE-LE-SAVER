<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard', pageTitle: 'Tableau de bord' })

const { getDashboard } = useAdmin()
const { data: stats } = await useAsyncData('admin-dashboard', () => getDashboard())

const periode = ref('mensuel')
const moisLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc']

const toQuarters = (arr) => [0, 1, 2, 3].map((q) => arr.slice(q * 3, q * 3 + 3).reduce((a, b) => a + b, 0))

const salesCategories = computed(() => {
  if (periode.value === 'annuel') return [String(new Date().getFullYear())]
  if (periode.value === 'trimestriel') return ['T1', 'T2', 'T3', 'T4']
  return moisLabels
})

const salesSeries = computed(() => {
  const ventes = stats.value?.monthly_sales || []
  if (periode.value === 'annuel') return [{ name: 'Chiffre d\'affaires', data: [ventes.reduce((a, b) => a + b, 0)] }]
  if (periode.value === 'trimestriel') return [{ name: 'Chiffre d\'affaires', data: toQuarters(ventes) }]
  return [{ name: 'Chiffre d\'affaires', data: ventes }]
})

const ordersSeries = computed(() => {
  const commandes = stats.value?.monthly_orders || []
  if (periode.value === 'annuel') return [{ name: 'Commandes', data: [commandes.reduce((a, b) => a + b, 0)] }]
  if (periode.value === 'trimestriel') return [{ name: 'Commandes', data: toQuarters(commandes) }]
  return [{ name: 'Commandes', data: commandes }]
})

const barOptions = computed(() => ({
  chart: { toolbar: { show: false }, fontFamily: 'inherit' },
  plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
  dataLabels: { enabled: false },
  colors: ['#1e3a7a'],
  xaxis: { categories: salesCategories.value, axisBorder: { show: false }, axisTicks: { show: false } },
  grid: { borderColor: '#f1f5f9' },
  yaxis: { labels: { formatter: (v) => `${(v / 1000).toFixed(0)}K` } },
}))

const areaOptions = computed(() => ({
  chart: { toolbar: { show: false }, fontFamily: 'inherit' },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  colors: ['#1e3a7a'],
  fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0 } },
  xaxis: { categories: salesCategories.value, axisBorder: { show: false }, axisTicks: { show: false } },
  grid: { borderColor: '#f1f5f9' },
}))

const statutColor = (statut) => ({
  en_attente: 'warning',
  paye: 'success',
  livre: 'success',
  annule: 'error',
}[statut] || 'neutral')
</script>

<template>
  <div class="p-6">
    <!-- Cartes de stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <UCard>
        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center mb-3">
          <UIcon name="i-lucide-wallet" class="w-5 h-5 text-primary" />
        </div>
        <p class="text-sm text-gray-500">Chiffre d'affaires</p>
        <p class="text-2xl font-bold mt-1">{{ Number(stats?.total_revenue ?? 0).toLocaleString('fr-FR') }} FCFA</p>
      </UCard>

      <UCard>
        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center mb-3">
          <UIcon name="i-lucide-users" class="w-5 h-5 text-primary" />
        </div>
        <p class="text-sm text-gray-500">Utilisateurs</p>
        <p class="text-2xl font-bold mt-1">{{ stats?.total_users ?? 0 }}</p>
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

    <!-- Alerte installations en attente -->
    <UCard v-if="stats?.installations_en_attente > 0" class="mb-6 border-warning/30 bg-warning/5">
      <div class="flex items-center gap-3">
        <UIcon name="i-lucide-alert-triangle" class="w-6 h-6 text-warning shrink-0" />
        <div class="flex-1">
          <p class="font-medium">{{ stats.installations_en_attente }} installation(s) sans technicien assigné</p>
          <p class="text-sm text-gray-500">Assignez un technicien pour ne pas retarder les interventions.</p>
        </div>
        <UButton to="/dashboard/admin/installations" variant="soft" size="sm">Voir</UButton>
      </div>
    </UCard>

    <!-- Graphiques -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="font-semibold">Chiffre d'affaires</h2>
            <div class="flex gap-1">
              <UButton
                v-for="p in [['mensuel','Mensuel'],['trimestriel','Trimestriel'],['annuel','Annuel']]"
                :key="p[0]"
                size="xs"
                :variant="periode === p[0] ? 'solid' : 'ghost'"
                @click="periode = p[0]"
              >
                {{ p[1] }}
              </UButton>
            </div>
          </div>
        </template>
        <ClientOnly>
          <apexchart type="bar" height="280" :options="barOptions" :series="salesSeries" />
        </ClientOnly>
      </UCard>

      <UCard>
        <template #header>
          <h2 class="font-semibold">Commandes</h2>
        </template>
        <ClientOnly>
          <apexchart type="area" height="280" :options="areaOptions" :series="ordersSeries" />
        </ClientOnly>
      </UCard>
    </div>

    <!-- Commandes récentes -->
    <UCard>
      <template #header>
        <h2 class="font-semibold">Commandes récentes</h2>
      </template>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500 border-b border-gray-200 dark:border-gray-800">
            <th class="pb-2">Client</th>
            <th class="pb-2">Total</th>
            <th class="pb-2">Statut</th>
            <th class="pb-2">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in stats?.recent_orders" :key="order.id" class="border-b border-gray-100 dark:border-gray-800 last:border-0">
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