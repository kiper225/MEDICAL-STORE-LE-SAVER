<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard', pageTitle: 'Mon espace' })

const authStore = useAuthStore()
const { getMyInstallations, updateInstallation } = useInstallations()
const toast = useToast()

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
  </div>
</template>