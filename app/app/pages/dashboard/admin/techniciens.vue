<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard' })

const { getTechnicians, createTechnician, deleteTechnician } = useTechnicians()
const toast = useToast()

const { data: technicians, refresh } = await useAsyncData('technicians', () => getTechnicians())

const showModal = ref(false)
const form = reactive({ nom: '', email: '', password: '', telephone: '' })
const submitting = ref(false)
const errorMessage = ref('')

const disponibiliteInfo = (count) => {
  if (count === 0) return { label: 'Disponible', color: 'success' }
  if (count <= 2) return { label: `${count} en cours`, color: 'warning' }
  return { label: `${count} en cours`, color: 'error' }
}

const handleCreate = async () => {
  errorMessage.value = ''
  submitting.value = true
  try {
    await createTechnician(form)
    toast.add({ title: 'Technicien ajouté', color: 'success' })
    showModal.value = false
    Object.assign(form, { nom: '', email: '', password: '', telephone: '' })
    refresh()
  } catch (err) {
    errorMessage.value = err.data?.message || 'Erreur lors de la création.'
  } finally {
    submitting.value = false
  }
}

const handleDelete = async (id) => {
  try {
    await deleteTechnician(id)
    toast.add({ title: 'Technicien supprimé', color: 'success' })
    refresh()
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  }
}
</script>

<template>
  <div class="p-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Techniciens</h1>
      <UButton icon="i-lucide-plus" @click="showModal = true">Ajouter un technicien</UButton>
    </div>

    <div v-if="technicians?.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <UCard v-for="tech in technicians" :key="tech.id">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <UAvatar :alt="tech.nom" size="md" />
            <div>
              <p class="font-medium">{{ tech.nom }}</p>
              <p class="text-xs text-gray-500">{{ tech.email }}</p>
              <p v-if="tech.telephone" class="text-xs text-gray-500">{{ tech.telephone }}</p>
            </div>
          </div>
          <UButton icon="i-lucide-trash-2" variant="ghost" color="error" size="xs" @click="handleDelete(tech.id)" />
        </div>

        <UBadge
          :color="disponibiliteInfo(tech.installations_en_cours).color"
          variant="subtle"
          class="mt-3"
        >
          {{ disponibiliteInfo(tech.installations_en_cours).label }}
        </UBadge>
      </UCard>
    </div>
    <p v-else class="text-gray-500">Aucun technicien enregistré.</p>

    <UModal v-model:open="showModal">
      <template #content>
        <UCard>
          <template #header>
            <h3 class="font-semibold">Nouveau technicien</h3>
          </template>

          <UForm :state="form" @submit="handleCreate" class="space-y-4">
            <UFormField label="Nom complet" required>
              <UInput v-model="form.nom" class="w-full" />
            </UFormField>
            <UFormField label="Email" required>
              <UInput v-model="form.email" type="email" class="w-full" />
            </UFormField>
            <UFormField label="Mot de passe" required>
              <UInput v-model="form.password" type="password" class="w-full" />
            </UFormField>
            <UFormField label="Téléphone">
              <UInput v-model="form.telephone" class="w-full" />
            </UFormField>

            <UAlert v-if="errorMessage" color="error" variant="soft" :title="errorMessage" />

            <UButton type="submit" block :loading="submitting">Créer le compte</UButton>
          </UForm>
        </UCard>
      </template>
    </UModal>
  </div>
</template>