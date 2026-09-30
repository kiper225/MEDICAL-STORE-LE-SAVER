export default defineNuxtPlugin(async () => {
  const authStore = useAuthStore()
  const token = useCookie('auth_token')

  if (token.value && !authStore.user) {
    await authStore.fetchUser()
  }
})