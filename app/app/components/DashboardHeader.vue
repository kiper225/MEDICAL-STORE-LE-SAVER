<script setup>
const props = defineProps({
  title: { type: String, default: '' },
  collapsed: { type: Boolean, default: false },
})
const emit = defineEmits(['update:collapsed'])

const authStore = useAuthStore()
const router = useRouter()
const handleLogout = () => authStore.logout()

const roleLabel = computed(() => ({
  admin: 'Administrateur',
  vendeur: 'Vendeur',
  technicien: 'Technicien',
  client: 'Client',
}[authStore.user?.role] || ''))

// --- Recherche ---
const { getProducts } = useProducts()
const searchQuery = ref('')
const searchResults = ref([])
const showResults = ref(false)
const searching = ref(false)
let debounceTimer = null
const searchInput = ref(null)

watch(searchQuery, (val) => {
  clearTimeout(debounceTimer)
  if (!val || val.trim().length < 2) {
    searchResults.value = []
    showResults.value = false
    return
  }
  debounceTimer = setTimeout(async () => {
    searching.value = true
    try {
      const res = await getProducts({ q: val, all: true, per_page: 6 })
      searchResults.value = res.data || []
      showResults.value = true
    } finally {
      searching.value = false
    }
  }, 300)
})

const goToProduct = (product) => {
  showResults.value = false
  searchQuery.value = ''
  const editBase = authStore.user?.role === 'admin' ? '/dashboard/admin' : '/dashboard/vendeur'
  router.push(`${editBase}/produits/${product.slug}/modifier`)
}

const closeResultsDelayed = () => setTimeout(() => { showResults.value = false }, 150)

// Raccourci Ctrl+K / Cmd+K
onMounted(() => {
  const handler = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault()
      searchInput.value?.inputRef?.focus?.()
    }
  }
  window.addEventListener('keydown', handler)
  onUnmounted(() => window.removeEventListener('keydown', handler))
})

const { getNotifications, markRead, markAllRead } = useNotifications()
const notifications = ref([])
const unreadCount = ref(0)

const fetchNotifications = async () => {
  const res = await getNotifications()
  notifications.value = res.notifications.data
  unreadCount.value = res.unread_count
}

onMounted(() => {
  fetchNotifications()
  const poll = setInterval(fetchNotifications, 30000)
  onUnmounted(() => clearInterval(poll))
})

const handleNotifClick = async (n) => {
  if (!n.read_at) {
    await markRead(n.id)
    unreadCount.value = Math.max(0, unreadCount.value - 1)
    n.read_at = new Date().toISOString()
  }
  const routes = {
    nouvelle_commande: '/dashboard/vendeur/commandes',
    installation_assignee: '/dashboard/technicien',
    statut_commande: '/dashboard/client',
  }
  router.push(routes[n.data?.type] || '/dashboard')
}

const handleMarkAllRead = async () => {
  await markAllRead()
  unreadCount.value = 0
  notifications.value = notifications.value.map((n) => ({ ...n, read_at: n.read_at || new Date().toISOString() }))
}
</script>

<template>
  <header class="h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center gap-4 px-4 md:px-6 shrink-0 z-10">
    <UButton
      :icon="collapsed ? 'i-lucide-panel-left-open' : 'i-lucide-panel-left-close'"
      variant="ghost"
      color="neutral"
      @click="emit('update:collapsed', !collapsed)"
    />

    <div class="relative flex-1 max-w-md">
      <UInput
        ref="searchInput"
        v-model="searchQuery"
        placeholder="Rechercher un produit..."
        icon="i-lucide-search"
        size="md"
        class="w-full"
        @focus="searchQuery.length >= 2 && (showResults = true)"
        @blur="closeResultsDelayed"
      >
        <template #trailing>
          <kbd class="hidden sm:inline text-xs text-gray-400 border border-gray-200 dark:border-gray-700 rounded px-1.5 py-0.5">⌘K</kbd>
        </template>
      </UInput>

      <div
        v-if="showResults"
        class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-xl z-50 max-h-72 overflow-y-auto"
      >
        <div v-if="searching" class="p-4 text-center text-sm text-gray-400">Recherche...</div>
        <template v-else-if="searchResults.length">
          <button
            v-for="product in searchResults"
            :key="product.id"
            class="w-full flex items-center gap-3 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800 text-left"
            @mousedown.prevent="goToProduct(product)"
          >
            <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded overflow-hidden shrink-0">
              <img v-if="product.images?.length" :src="product.images[0].url" class="w-full h-full object-cover" />
            </div>
            <span class="text-sm truncate">{{ product.nom }}</span>
          </button>
        </template>
        <div v-else class="p-4 text-center text-sm text-gray-400">Aucun résultat.</div>
      </div>
    </div>

    <h1 class="hidden lg:block font-semibold text-lg mr-auto">{{ title }}</h1>

    <div class="flex items-center gap-2">
      <ClientOnly>
        <UButton
          :icon="$colorMode.value === 'dark' ? 'i-lucide-sun' : 'i-lucide-moon'"
          variant="ghost"
          color="neutral"
          @click="$colorMode.preference = $colorMode.value === 'dark' ? 'light' : 'dark'"
        />
      </ClientOnly>

      <UPopover>
        <UButton icon="i-lucide-bell" variant="ghost" color="neutral" class="relative">
          <UChip
            v-if="unreadCount > 0"
            :text="unreadCount"
            color="error"
            :ui="{ base: 'text-[10px] font-bold min-w-[18px] h-[18px] px-1.5 py-0.5 m-1' }"
          />
        </UButton>

        <template #content>
          <div class="w-80 max-h-96 overflow-y-auto">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-800">
              <span class="font-semibold text-sm">Notifications</span>
              <button v-if="unreadCount > 0" class="text-xs text-primary hover:underline" @click="handleMarkAllRead">
                Tout marquer lu
              </button>
            </div>

            <template v-if="notifications.length">
              <button
                v-for="n in notifications"
                :key="n.id"
                class="w-full text-left px-4 py-3 border-b border-gray-100 dark:border-gray-800 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-800"
                :class="{ 'bg-primary/5': !n.read_at }"
                @click="handleNotifClick(n)"
              >
                <p class="text-sm">{{ n.data.message }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ new Date(n.created_at).toLocaleString('fr-FR') }}</p>
              </button>
            </template>
            <p v-else class="text-sm text-gray-400 text-center py-6">Aucune notification.</p>
          </div>
        </template>
      </UPopover>

      <ClientOnly>
        <UDropdownMenu
          :items="[[
            { label: authStore.user?.nom, disabled: true },
            { label: roleLabel, disabled: true },
          ], [
            { label: 'Mon profil', to: '/dashboard/profil', icon: 'i-lucide-user' },
            { label: 'Voir le site', to: '/', icon: 'i-lucide-external-link' },
            { label: 'Déconnexion', icon: 'i-lucide-log-out', onSelect: handleLogout },
          ]]"
        >
          <button class="flex items-center gap-2">
            <UAvatar :src="authStore.user?.photo_url" :alt="authStore.user?.nom" size="sm" />
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