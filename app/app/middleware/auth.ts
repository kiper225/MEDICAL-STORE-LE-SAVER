export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore()

  if (!authStore.user) {
    await authStore.fetchUser()
  }

  if (!authStore.user) {
    return navigateTo('/login')
  }
})