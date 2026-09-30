<script setup>

const authStore = useAuthStore()
const route = useRoute()
const pageTitle = computed(() => route.meta.pageTitle || 'Tableau de bord')

const navByRole = {
  admin: [
    { label: 'Tableau de bord', to: '/dashboard/admin', icon: 'i-lucide-layout-dashboard' },
    { label: 'Utilisateurs', to: '/dashboard/admin/utilisateurs', icon: 'i-lucide-users' },
    { label: 'Techniciens', to: '/dashboard/admin/techniciens', icon: 'i-lucide-hard-hat' },
    { label: 'Produits', to: '/dashboard/admin/produits', icon: 'i-lucide-package' },
    { label: 'Commandes', to: '/dashboard/admin/commandes', icon: 'i-lucide-shopping-cart' },
    { label: 'Installations', to: '/dashboard/admin/installations', icon: 'i-lucide-wrench' },
],
  vendeur: [
    { label: 'Mes produits', to: '/dashboard/vendeur', icon: 'i-lucide-package' },
    { label: 'Mes commandes', to: '/dashboard/vendeur/commandes', icon: 'i-lucide-shopping-cart' },
  ],
  technicien: [
    { label: 'Mes interventions', to: '/dashboard/technicien', icon: 'i-lucide-wrench' },
  ],
  client: [
    { label: 'Mon espace', to: '/dashboard/client', icon: 'i-lucide-layout-dashboard' },
  ],
}

const navItems = computed(() => navByRole[authStore.user?.role] || [])

const handleLogout = () => authStore.logout()
</script>

<template>
  <div class="min-h-screen flex bg-gray-50 dark:bg-gray-950">
    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-gray-900 border-r flex flex-col shrink-0">
      <div class="h-16 flex items-center gap-2 px-6 border-b">
        <img src="../assets/img/logo.png" class="h-8 w-8" />
        <span class="font-semibold text-primary text-sm">Medical Store</span>
      </div>

      <nav class="flex-1 px-3 py-4 space-y-1">
        <NuxtLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
          :class="route.path === item.to
            ? 'bg-primary/10 text-primary'
            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'"
        >
          <UIcon :name="item.icon" class="w-5 h-5" />
          {{ item.label }}
        </NuxtLink>
      </nav>

      <div class="p-4 border-t flex items-center gap-3">
        <UAvatar :src="authStore.user?.photo_url" :alt="authStore.user?.nom" size="sm" />
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium truncate">{{ authStore.user?.nom }}</p>
          <p class="text-xs text-gray-500 capitalize">{{ authStore.user?.role }}</p>
        </div>
      </div>
    </aside>
    
    <!-- Contenu -->
    <main class="flex-1 flex flex-col overflow-hidden">
      <DashboardHeader :title="pageTitle" />
      <div class="flex-1 overflow-y-auto">
        <slot />
      </div>
    </main>
  </div>
</template>