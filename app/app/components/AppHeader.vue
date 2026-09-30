<script setup>
const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()

const { getCategories } = useCategories()
const { getProducts } = useProducts()
const { data: categoriesTree } = await useAsyncData('header-categories', () => getCategories())

const dashboardLink = computed(() => {
  const links = {
    admin: '/dashboard/admin',
    vendeur: '/dashboard/vendeur',
    technicien: '/dashboard/technicien',
    client: '/dashboard/client',
  }
  return links[authStore.user?.role] || '/'
})

const handleLogout = () => authStore.logout()

const activeParent = ref(null)
const mobileMenuOpen = ref(false)

// --- Recherche live ---
const searchQuery = ref('')
const searchResults = ref([])
const showResults = ref(false)
const searching = ref(false)
let debounceTimer = null

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
      const res = await getProducts({ q: val, per_page: 6 })
      searchResults.value = res.data || []
      showResults.value = true
    } finally {
      searching.value = false
    }
  }, 300)
})

const goToProduct = (slug) => {
  showResults.value = false
  searchQuery.value = ''
  router.push(`/produits/${slug}`)
}

const handleSearch = () => {
  showResults.value = false
  router.push({ path: '/produits', query: { q: searchQuery.value } })
}

const closeResultsDelayed = () => {
  setTimeout(() => { showResults.value = false }, 150)
}
</script>

<template>
  <header class="sticky top-0 z-20">
    <!-- Barre du haut -->
    <div class="bg-primary text-white text-xs hidden sm:block">
      <div class="max-w-7xl mx-auto px-4 h-9 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <a href="tel:+2250574480000" class="flex items-center gap-1.5 hover:opacity-80">
            <UIcon name="i-lucide-phone" class="w-3.5 h-3.5" />
            +225 05 74 48 00 00
          </a>
          <span>Assistance 24h/7</span>
        </div>
        <div class="flex items-center gap-4">
          <NuxtLink to="/register?role=vendeur" class="hover:opacity-80">Devenir vendeur</NuxtLink>
          <div class="flex items-center gap-2">
            <UIcon name="i-simple-icons-facebook" class="w-3.5 h-3.5 cursor-pointer hover:opacity-80" />
            <UIcon name="i-simple-icons-instagram" class="w-3.5 h-3.5 cursor-pointer hover:opacity-80" />
            <UIcon name="i-simple-icons-x" class="w-3.5 h-3.5 cursor-pointer hover:opacity-80" />
          </div>
        </div>
      </div>
    </div>

    <!-- Header principal -->
    <div class="bg-white dark:bg-gray-900">
      <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center gap-3">
        <!-- Hamburger mobile -->
        <UButton
          icon="i-lucide-menu"
          variant="ghost"
          color="neutral"
          class="md:hidden order-1"
          @click="mobileMenuOpen = true"
        />

        <NuxtLink to="/" class="flex items-center gap-2 shrink-0 order-2">
          <img src="../assets/img/logo.png" alt="Medical Store Dieu Sauveur" class="h-12 md:h-15" />
        </NuxtLink>

        <!-- Recherche -->
        <div class="relative flex-1 min-w-[140px] order-4 md:order-3 basis-full md:basis-auto">
          <UInput
            v-model="searchQuery"
            placeholder="Rechercher un équipement médical..."
            class="w-full"
            size="lg"
            @keyup.enter="handleSearch"
            @focus="searchQuery.length >= 2 && (showResults = true)"
            @blur="closeResultsDelayed"
          >
            <template #trailing>
              <UButton icon="i-lucide-search" size="sm" @click="handleSearch" />
            </template>
          </UInput>

          <!-- Dropdown résultats -->
          <div
            v-if="showResults"
            class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-xl z-50 max-h-80 overflow-y-auto"
          >
            <div v-if="searching" class="p-4 text-center text-sm text-gray-400">Recherche...</div>
            <template v-else-if="searchResults.length">
              <button
                v-for="product in searchResults"
                :key="product.id"
                class="w-full flex items-center gap-3 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800 text-left"
                @mousedown.prevent="goToProduct(product.slug)"
              >
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded overflow-hidden shrink-0">
                  <img v-if="product.images?.length" :src="product.images[0].url" class="w-full h-full object-cover" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium truncate">{{ product.nom }}</p>
                  <p v-if="product.prix_vente" class="text-xs text-primary font-semibold">
                    {{ Number(product.prix_vente).toLocaleString('fr-FR') }} FCFA
                  </p>
                </div>
              </button>
              <button
                class="w-full text-center text-sm text-primary py-2 border-t border-gray-100 dark:border-gray-800 hover:underline"
                @mousedown.prevent="handleSearch"
              >
                Voir tous les résultats
              </button>
            </template>
            <div v-else class="p-4 text-center text-sm text-gray-400">Aucun résultat.</div>
          </div>
        </div>

        <div class="hidden lg:flex items-center gap-2 text-sm shrink-0 order-3 md:order-4">
          <UIcon name="i-lucide-headset" class="w-8 h-8 text-primary" />
          <div class="leading-tight">
            <p class="text-gray-400 text-xs">Assistance 24h/7</p>
            <p class="font-semibold text-primary">+225 05 74 48 00 00</p>
          </div>
        </div>

        <ClientOnly>
          <UButton to="/panier" variant="ghost" class="relative order-3 md:order-5">
            <UIcon name="i-lucide-shopping-cart" class="w-7 h-7 md:w-8 md:h-8" />
            <UChip v-if="cartStore.totalItems > 0" :text="cartStore.totalItems" size="md" color="primary" />
            <span v-if="cartStore.totalItems > 0" class="ml-1 text-sm font-semibold hidden md:inline">
              {{ cartStore.totalPrice.toLocaleString('fr-FR') }} FCFA
            </span>
          </UButton>
          <template #fallback>
            <div class="h-10 w-10 order-3 md:order-5" />
          </template>
        </ClientOnly>

        <ClientOnly>
          <template v-if="authStore.user">
            <UDropdownMenu
              :items="[[
                { label: authStore.user.nom, disabled: true },
              ], [
                { label: 'Mon profil', to: '/dashboard/profil', icon: 'i-lucide-user' },
                { label: 'Mon espace', to: dashboardLink, icon: 'i-lucide-layout-dashboard' },
                { label: 'Déconnexion', icon: 'i-lucide-log-out', onSelect: handleLogout },
              ]]"
              class="order-3 md:order-6"
            >
              <button class="flex items-center gap-1.5">
                <UAvatar :src="authStore.user.photo_url" :alt="authStore.user.nom" class="size-12" />
              </button>
            </UDropdownMenu>
          </template>
          <template v-else>
            <UDropdownMenu
              :items="[[
                { label: 'Connexion', to: '/login', icon: 'i-lucide-user' },
                { label: 'Inscription', to: '/register?role=client', icon: 'i-lucide-user-plus' },
              ]]"
              class="order-3 md:order-6"
            >
              <UButton variant="ghost" icon="i-lucide-user-circle" size="lg" />
            </UDropdownMenu>
          </template>
          <template #fallback>
            <div class="h-10 w-10 rounded-full bg-gray-100 dark:bg-gray-800 animate-pulse order-3 md:order-6" />
          </template>
        </ClientOnly>
      </div>

      <!-- Barre catégories : desktop uniquement -->
      <div class="hidden md:block border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
        <nav class="max-w-7xl mx-auto px-4 h-11 flex items-center justify-center gap-8">
          <div
            v-for="parent in categoriesTree"
            :key="parent.slug"
            class="relative h-full flex items-center"
            @mouseenter="activeParent = parent.slug"
            @mouseleave="activeParent = null"
          >
            <button
              class="text-sm font-medium whitespace-nowrap px-1"
              :class="activeParent === parent.slug ? 'text-primary' : 'text-gray-700 dark:text-gray-200 hover:text-primary'"
            >
              {{ parent.nom }}
            </button>

            <Transition name="fade">
              <div
                v-if="activeParent === parent.slug && parent.children?.length"
                class="absolute top-full left-1/2 -translate-x-1/2 w-64 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-b-lg shadow-xl z-40 py-3"
              >
                <NuxtLink
                  v-for="child in parent.children"
                  :key="child.slug"
                  :to="`/produits?categorie=${child.slug}`"
                  class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-primary"
                >
                  {{ child.nom }}
                </NuxtLink>
              </div>
            </Transition>
          </div>
        </nav>
      </div>
    </div>

    <!-- Menu mobile (slide-over) -->
    <USlideover v-model:open="mobileMenuOpen" side="left">
      <template #content>
        <div class="p-4">
          <div class="flex items-center justify-between mb-4">
            <span class="font-semibold">Catégories</span>
            <UButton icon="i-lucide-x" variant="ghost" size="sm" @click="mobileMenuOpen = false" />
          </div>
          <div class="space-y-1">
            <div v-for="parent in categoriesTree" :key="parent.slug">
              <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 px-2 py-2">{{ parent.nom }}</p>
              <NuxtLink
                v-for="child in parent.children"
                :key="child.slug"
                :to="`/produits?categorie=${child.slug}`"
                class="block px-4 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:text-primary"
                @click="mobileMenuOpen = false"
              >
                {{ child.nom }}
              </NuxtLink>
            </div>
          </div>
        </div>
      </template>
    </USlideover>
  </header>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>