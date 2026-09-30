<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard' })

const { getInstallations, assignTechnician } = useInstallationsAdmin()
const { getTechnicians } = useTechnicians()
const toast = useToast()

const { data: installations, refresh } = await useAsyncData('admin-installations', () => getInstallations())
const { data: technicians } = await useAsyncData('technicians-for-assign', () => getTechnicians())

const technicianOptions = computed(() =>
  technicians.value?.map((t) => ({ label: t.nom, value: t.id })) || []
)

const statutColor = (statut) => ({
  planifiee: 'warning',
  en_cours: 'info',
  terminee: 'success',
  annulee: 'error',
}[statut] || 'neutral')

const handleAssign = async (installationId, technicienId) => {
  try {
    await assignTechnician(installationId, technicienId)
    toast.add({ title: 'Technicien assigné', color: 'success' })
    refresh()
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  }
}
</script>

<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-6">Installations</h1>

    <div v-if="installations?.data?.length" class="space-y-3">
      <UCard v-for="installation in installations.data" :key="installation.id">
        <div class="flex items-center justify-between gap-4">
          <div class="flex-1">
            <p class="font-medium">
              {{ installation.rental?.product?.nom || 'Commande directe' }}
            </p>
            <p class="text-sm text-gray-500">
              {{ installation.adresse_installation }} · Prévu le {{ installation.date_prevue }}
            </p>
            <p class="text-xs text-gray-400">
              Client : {{ installation.rental?.user?.nom || installation.order?.user?.nom }}
            </p>
          </div>

          <UBadge :color="statutColor(installation.statut)" variant="subtle">
            {{ installation.statut }}
          </UBadge>

          <USelect
            :model-value="installation.technicien_id"
            :items="technicianOptions"
            placeholder="Assigner un technicien"
            class="w-48"
            @update:model-value="(val) => handleAssign(installation.id, val)"
          />
        </div>
      </UCard>
    </div>
    <p v-else class="text-gray-500">Aucune installation pour le moment.</p>
  </div>
</template>