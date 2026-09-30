<script setup>
const nom = ref('')
const email = ref('')
const telephone = ref('')
const adresse = ref('')
const genre = ref(null)
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const conditions = ref(false)
const errorMessage = ref('')
const loading = ref(false)

const photoFile = ref(null)
const photoPreview = ref(null)
const photoInput = ref(null)

const router = useRouter()
const authStore = useAuthStore()
const config = useRuntimeConfig()

const triggerPhotoInput = () => photoInput.value?.click()

const handlePhotoChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    photoFile.value = file
    photoPreview.value = URL.createObjectURL(file)
  }
}

const handleRegister = async () => {
  errorMessage.value = ''

  if (!conditions.value) {
    errorMessage.value = 'Vous devez accepter les conditions d\'achat et de location.'
    return
  }

  loading.value = true
  try {
    const formData = new FormData()
    formData.append('nom', nom.value)
    formData.append('email', email.value)
    formData.append('telephone', telephone.value)
    formData.append('adresse', adresse.value)
    if (genre.value) formData.append('genre', genre.value)
    formData.append('password', password.value)
    formData.append('password_confirmation', passwordConfirmation.value)
    formData.append('role', 'client')
    formData.append('conditions', conditions.value ? '1' : '0')
    if (photoFile.value) formData.append('photo', photoFile.value)

    const api = useApi()
    const response = await api('/register', { method: 'POST', body: formData })

    const token = useCookie('auth_token', { maxAge: 60 * 60 * 24 * 7, sameSite: 'lax' })
    token.value = response.token
    authStore.user = response.user

    router.push('/dashboard/client')
  } catch (error) {
    errorMessage.value = error.data?.message || 'Erreur lors de l\'inscription.'
  } finally {
    loading.value = false
  }
}

const registerWithGoogle = () => {
  window.location.href = `${config.public.apiBase}/auth/google/redirect`
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-white dark:bg-gray-950 px-4 py-12">
    <div class="w-full max-w-md">
      <h1 class="text-3xl font-bold text-center mb-10">Créer un compte</h1>

      <form @submit.prevent="handleRegister" class="space-y-5">
        <!-- Photo de profil -->
        <div class="flex flex-col items-center gap-2">
          <button
            type="button"
            class="relative w-24 h-24 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden border border-gray-200 dark:border-gray-700"
            @click="triggerPhotoInput"
          >
            <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
            <div v-else class="w-full h-full flex items-center justify-center">
              <UIcon name="i-lucide-user" class="w-10 h-10 text-gray-400" />
            </div>
            <div class="absolute bottom-0 inset-x-0 bg-primary/90 text-white text-center py-1">
              <UIcon name="i-lucide-camera" class="w-4 h-4" />
            </div>
          </button>
          <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="handlePhotoChange" />
          <p class="text-xs text-gray-400">Photo de profil (optionnel)</p>
        </div>

        <div>
          <label class="block text-sm text-gray-500 mb-1.5">Nom complet :</label>
          <UInput v-model="nom" placeholder="Votre nom complet" size="lg" class="w-full" />
        </div>

        <div>
          <label class="block text-sm text-gray-500 mb-1.5">Adresse email :</label>
          <UInput v-model="email" type="email" placeholder="Adresse email" size="lg" class="w-full" />
        </div>

        <div>
          <label class="block text-sm text-gray-500 mb-1.5">Numéro de téléphone :</label>
          <UInput v-model="telephone" placeholder="07 XX XX XX XX" size="lg" class="w-full" />
        </div>

        <div>
          <label class="block text-sm text-gray-500 mb-1.5">Genre :</label>
          <div class="flex gap-4">
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" v-model="genre" value="homme" class="accent-primary" />
              Homme
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" v-model="genre" value="femme" class="accent-primary" />
              Femme
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" v-model="genre" value="autre" class="accent-primary" />
              Autre
            </label>
          </div>
        </div>

        <div>
          <label class="block text-sm text-gray-500 mb-1.5">Adresse :</label>
          <UInput v-model="adresse" placeholder="Votre adresse (ville, commune)" size="lg" class="w-full" />
        </div>

        <div>
          <label class="block text-sm text-gray-500 mb-1.5">Mot de passe :</label>
          <UInput
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="Mot de passe"
            size="lg"
            class="w-full"
          >
            <template #trailing>
              <button type="button" @click="showPassword = !showPassword">
                <UIcon :name="showPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'" class="w-5 h-5 text-gray-400" />
              </button>
            </template>
          </UInput>
        </div>

        <div>
          <label class="block text-sm text-gray-500 mb-1.5">Confirmer le mot de passe :</label>
          <UInput v-model="passwordConfirmation" type="password" placeholder="Confirmer le mot de passe" size="lg" class="w-full" />
        </div>

        <label class="flex items-start gap-2 text-sm cursor-pointer">
          <input type="checkbox" v-model="conditions" class="accent-primary mt-0.5" />
          <span class="text-gray-600 dark:text-gray-300">
            J'accepte les <NuxtLink to="/conditions" class="text-primary hover:underline">conditions d'achat et de location</NuxtLink> de Medical Store Dieu Sauveur.
          </span>
        </label>

        <UAlert v-if="errorMessage" color="error" variant="soft" :title="errorMessage" />

        <div class="flex justify-center pt-2">
          <UButton
            type="submit"
            :loading="loading"
            size="lg"
            trailing-icon="i-lucide-chevron-right"
            class="rounded-full px-8"
          >
            Je m'inscris
          </UButton>
        </div>
      </form>

      <UButton
        block
        variant="outline"
        color="neutral"
        icon="i-logos-google-icon"
        class="mt-4"
        @click="registerWithGoogle"
      >
        S'inscrire avec Google
      </UButton>

      <div class="border-t border-gray-200 dark:border-gray-800 my-8"></div>

      <div class="text-center">
        <p class="text-sm text-gray-500 mb-4">Déjà un compte ?</p>
        <UButton to="/login" size="lg" trailing-icon="i-lucide-chevron-right" class="rounded-full px-8">
          Connexion
        </UButton>
      </div>
    </div>
  </div>
</template>