export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()
  const allowedRole = to.path.split('/')[2] // ex: "admin" depuis /dashboard/admin

  if (authStore.user && authStore.user.role !== allowedRole && authStore.user.role !== 'admin') {
    return navigateTo('/') // ou une page 403 dédiée
  }
})