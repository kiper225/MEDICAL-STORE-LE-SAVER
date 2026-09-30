<script setup>
const route = useRoute()
const authStore = useAuthStore()

onMounted(async () => {
  const token = route.query.token
  if (token) {
    const cookie = useCookie('auth_token', { maxAge: 60 * 60 * 24 * 7, sameSite: 'lax' })
    cookie.value = token
    await authStore.fetchUser()
    navigateTo('/dashboard/client')
  } else {
    navigateTo('/login')
  }
})
</script>

<template>
  <div class="min-h-screen flex items-center justify-center">
    <p class="text-gray-500">Connexion en cours...</p>
  </div>
</template>