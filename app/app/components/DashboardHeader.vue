<script setup>
defineProps({ title: { type: String, default: '' } })

const authStore = useAuthStore()
const handleLogout = () => authStore.logout()

const roleLabel = computed(() => ({
  admin: 'Administrateur',
  vendeur: 'Vendeur',
  technicien: 'Technicien',
  client: 'Client',
}[authStore.user?.role] || ''))
</script>

<template>
  <header class="h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between px-6 sticky top-0 z-10 shrink-0">
    <div>
      <h1 class="font-semibold text-lg">{{ title }}</h1>
    </div>

    <div class="flex items-center gap-3">
      <NuxtLink to="/" class="gap-2 shrink-0 order-2">
      <img src="../assets/img/logo.png" alt="Medical Store Dieu Sauveur" class="h-12 md:h-15" />
    </NuxtLink>
      <UButton icon="i-lucide-bell" variant="ghost" color="neutral" />

      <ClientOnly>
        <UDropdownMenu
          :items="[[
            { label: authStore.user?.nom, disabled: true },
            { label: roleLabel, disabled: true },
          ], [
            { label: 'Voir le site', to: '/', icon: 'i-lucide-external-link' },
            { label: 'Déconnexion', icon: 'i-lucide-log-out', onSelect: handleLogout },
            { label: 'Mon profil', to: '/dashboard/profil', icon: 'i-lucide-user' },
          ]]"
        >
          <button class="flex items-center gap-2">
            <UAvatar :src="authStore.user?.photo_url" :alt="authStore.user?.nom" class="size-10" />
            <span class="text-sm font-medium hidden sm:inline">{{ authStore.user?.nom }}</span>
            <UIcon name="i-lucide-chevron-down" class="w-4 h-4 text-gray-400" />
          </button>
        </UDropdownMenu>
        <template #fallback>
          <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 animate-pulse" />
        </template>
      </ClientOnly>
    </div>
  </header>
</template>