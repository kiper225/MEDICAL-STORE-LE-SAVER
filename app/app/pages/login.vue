<script setup>
const email = ref('')
const password = ref('')
const showPassword = ref(false)
const errorMessage = ref('')
const loading = ref(false)

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const config = useRuntimeConfig()

const handleLogin = async () => {
  errorMessage.value = ''
  loading.value = true

  try {
    await authStore.login(email.value, password.value)

    if (route.query.redirect) {
      router.push(route.query.redirect)
      return
    }

    const redirects = {
      admin: '/dashboard/admin',
      vendeur: '/dashboard/vendeur',
      technicien: '/dashboard/technicien',
      client: '/dashboard/client',
    }
    router.push(redirects[authStore.user.role] || '/')
  } catch (error) {
    errorMessage.value = error.data?.message || 'Identifiants invalides.'
  } finally {
    loading.value = false
  }
}

const registerWithGoogle = () => {
  window.location.href = `${config.public.apiBase}/auth/google/redirect`
}
</script>

<template>
  <div class="flex items-center justify-center bg-white dark:bg-gray-950 px-4 py-12">
    <div class="w-full max-w-md">
      <h1 class="text-3xl font-bold text-center mb-10">Connexion</h1>

      <form @submit.prevent="handleLogin" class="space-y-5">
        <div>
          <label class="block text-sm text-gray-500 mb-1.5">Adresse email :</label>
          <UInput
            v-model="email"
            type="email"
            placeholder="Adresse email"
            size="lg"
            class="w-full"
          />
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

        <div class="text-right">
          <NuxtLink to="/mot-de-passe-oublie" class="text-sm text-primary hover:underline">
            Mot de passe oublié
          </NuxtLink>
        </div>

        <UAlert v-if="errorMessage" color="error" variant="soft" :title="errorMessage" />

        <div class="flex justify-center pt-2">
          <UButton
            type="submit"
            :loading="loading"
            size="lg"
            trailing-icon="i-lucide-chevron-right"
            class="rounded-full px-8"
          >
            Connexion
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
        Continuer avec Google
      </UButton>

      <div class="border-t border-gray-200 dark:border-gray-800 my-8"></div>

      <div class="text-center">
        <h2 class="text-xl font-bold mb-2">Nouveau client ? Créez un compte</h2>
        <p class="text-sm text-gray-500 mb-5">
          Pour commander et accéder à nos services,<br />
          créez un compte Medical Store Dieu Sauveur
        </p>
        <UButton
          to="/register"
          size="lg"
          trailing-icon="i-lucide-chevron-right"
          class="rounded-full px-8"
        >
          Je m'inscris
        </UButton>
      </div>
    </div>
  </div>
</template>