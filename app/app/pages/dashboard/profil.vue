<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard', pageTitle: 'Mon profil' })

const authStore = useAuthStore()
const { updateProfile } = useProfile()
const toast = useToast()

const nom = ref(authStore.user?.nom || '')
const telephone = ref(authStore.user?.telephone || '')
const adresse = ref(authStore.user?.adresse || '')
const genre = ref(authStore.user?.genre || null)

const currentPassword = ref('')
const newPassword = ref('')
const newPasswordConfirmation = ref('')
const showPasswordSection = ref(false)

const photoFile = ref(null)
const photoPreview = ref(authStore.user?.photo_url || null)
const photoInput = ref(null)

const submitting = ref(false)
const errorMessage = ref('')

const triggerPhotoInput = () => photoInput.value?.click()

const handlePhotoChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    photoFile.value = file
    photoPreview.value = URL.createObjectURL(file)
  }
}

const handleSubmit = async () => {
  errorMessage.value = ''
  submitting.value = true

  try {
    const formData = new FormData()
    formData.append('nom', nom.value)
    formData.append('telephone', telephone.value)
    formData.append('adresse', adresse.value)
    if (genre.value) formData.append('genre', genre.value)
    if (photoFile.value) formData.append('photo', photoFile.value)

    if (showPasswordSection.value && newPassword.value) {
      formData.append('current_password', currentPassword.value)
      formData.append('password', newPassword.value)
      formData.append('password_confirmation', newPasswordConfirmation.value)
    }

    const updated = await updateProfile(formData)
    authStore.user = updated

    toast.add({ title: 'Profil mis à jour', color: 'success' })
    currentPassword.value = ''
    newPassword.value = ''
    newPasswordConfirmation.value = ''
    showPasswordSection.value = false
  } catch (err) {
    errorMessage.value = err.data?.message || 'Erreur lors de la mise à jour.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="max-w-2xl mx-auto p-8">
    <UCard>
      <template #header>
        <h2 class="font-semibold">Informations personnelles</h2>
      </template>

      <form @submit.prevent="handleSubmit" class="space-y-5">
        <div class="flex flex-col items-center gap-2">
          <button
            type="button"
            class="relative w-24 h-24 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden border border-gray-200 dark:border-gray-700"
            @click="triggerPhotoInput"
          >
            <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
            <div v-else class="w-full h-full flex items-center justify-center">
              <UIcon name="i-lucide-user" class="w-12 h-12 text-gray-400" />
            </div>
            <div class="absolute bottom-0 inset-x-0 bg-primary/90 text-white text-center py-1">
              <UIcon name="i-lucide-camera" class="w-4 h-4" />
            </div>
          </button>
          <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="handlePhotoChange" />
        </div>

        <UFormField label="Nom complet">
          <UInput v-model="nom" class="w-full" />
        </UFormField>

        <UFormField label="Email">
          <UInput :model-value="authStore.user?.email" disabled class="w-full" />
        </UFormField>

        <UFormField label="Téléphone">
          <UInput v-model="telephone" class="w-full" />
        </UFormField>

        <UFormField label="Adresse">
          <UInput v-model="adresse" class="w-full" />
        </UFormField>

        <UFormField label="Genre">
          <div class="flex gap-4">
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" v-model="genre" value="homme" class="accent-primary" /> Homme
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" v-model="genre" value="femme" class="accent-primary" /> Femme
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" v-model="genre" value="autre" class="accent-primary" /> Autre
            </label>
          </div>
        </UFormField>

        <div class="border-t border-gray-200 dark:border-gray-800 pt-4">
          <button
            type="button"
            class="text-sm text-primary hover:underline flex items-center gap-1"
            @click="showPasswordSection = !showPasswordSection"
          >
            <UIcon name="i-lucide-lock" class="w-4 h-4" />
            Changer le mot de passe
          </button>

          <div v-if="showPasswordSection" class="space-y-4 mt-4">
            <UFormField label="Mot de passe actuel">
              <UInput v-model="currentPassword" type="password" class="w-full" />
            </UFormField>
            <UFormField label="Nouveau mot de passe">
              <UInput v-model="newPassword" type="password" class="w-full" />
            </UFormField>
            <UFormField label="Confirmer le nouveau mot de passe">
              <UInput v-model="newPasswordConfirmation" type="password" class="w-full" />
            </UFormField>
          </div>
        </div>

        <UAlert v-if="errorMessage" color="error" variant="soft" :title="errorMessage" />

        <UButton type="submit" block :loading="submitting">
          Enregistrer les modifications
        </UButton>
      </form>
    </UCard>
  </div>
</template>