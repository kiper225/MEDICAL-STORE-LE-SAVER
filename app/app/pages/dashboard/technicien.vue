<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard', pageTitle: 'Mon espace' })

const authStore = useAuthStore()
const { getMyInstallations, updateInstallation } = useInstallations()
const toast = useToast()
const { getTechnicienDashboard } = useInstallations()
const { data: techStats } = await useAsyncData('tech-stats', () => getTechnicienDashboard())


const { data: installations, refresh } = await useAsyncData('my-installations', () =>
  getMyInstallations()
)

const statutColor = (statut) => ({
  planifiee: 'warning',
  en_cours: 'info',
  terminee: 'success',
  annulee: 'error',
}[statut] || 'neutral')

const marquerTerminee = async (id) => {
  try {
    await updateInstallation(id, { statut: 'terminee' })
    toast.add({ title: 'Installation marquée comme terminée', color: 'success' })
    refresh()
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  }
}
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-primary mb-1">Mes interventions</h1>
    <p class="text-gray-500 mb-6">{{ authStore.user?.nom }}</p>

    <div v-if="installations?.data?.length" class="space-y-3">
      <UCard v-for="installation in installations.data" :key="installation.id">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium">
              {{ installation.rental?.product?.nom || 'Produit — commande directe' }}
            </p>
            <p class="text-sm text-gray-500">
              {{ installation.adresse_installation }} · Prévu le {{ installation.date_prevue }}
            </p>
          </div>
          <div class="flex items-center gap-3">
            <UBadge :color="statutColor(installation.statut)" variant="subtle">
              {{ installation.statut }}
            </UBadge>
            <UButton
              v-if="installation.statut !== 'terminee'"
              size="sm" variant="soft" color="success"
              @click="marquerTerminee(installation.id)"
            >
              Marquer terminée
            </UButton>
          </div>
        </div>
      </UCard>
    </div>
    <p v-else class="text-gray-500">Aucune intervention assignée pour le moment.</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <UCard>
        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center mb-3">
          <UIcon name="i-lucide-clipboard-list" class="w-5 h-5 text-primary" />
        </div>
        <p class="text-sm text-gray-500">Total interventions</p>
        <p class="text-2xl font-bold mt-1">{{ techStats?.total_installations ?? 0 }}</p>
      </UCard>
      <UCard>
        <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center mb-3">
          <UIcon name="i-lucide-clock" class="w-5 h-5 text-warning" />
        </div>
        <p class="text-sm text-gray-500">En attente</p>
        <p class="text-2xl font-bold mt-1">{{ techStats?.en_attente ?? 0 }}</p>
      </UCard>
      <UCard>
        <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center mb-3">
          <UIcon name="i-lucide-check-circle" class="w-5 h-5 text-success" />
        </div>
        <p class="text-sm text-gray-500">Terminées</p>
        <p class="text-2xl font-bold mt-1">{{ techStats?.terminees ?? 0 }}</p>
      </UCard>
    </div>
  </div>
</template>