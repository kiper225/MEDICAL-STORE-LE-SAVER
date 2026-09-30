<script setup>
const route = useRoute()
const router = useRouter()

const { getProducts } = useProducts()
const { getCategories } = useCategories()

const { data: categoriesTree } = await useAsyncData('categories-filter', () => getCategories())

const selectedCategory = ref(route.query.category_slug || null)
const selectedType = ref(route.query.type_disponibilite || null)

const { data: products, refresh, pending } = await useAsyncData(
  'products-filtered',
  () => getProducts({
    category_slug: selectedCategory.value,
    type_disponibilite: selectedType.value,
  }),
  { watch: [selectedCategory, selectedType] }
)

const applyCategory = (slug) => {
  selectedCategory.value = slug === null ? null : (selectedCategory.value === slug ? null : slug)
  router.push({ query: { ...route.query, category_slug: selectedCategory.value || undefined } })
}

const applyType = (type) => {
  selectedType.value = selectedType.value === type ? null : type
  router.push({ query: { ...route.query, type_disponibilite: selectedType.value || undefined } })
}

const clearFilters = () => {
  selectedCategory.value = null
  selectedType.value = null
  router.push({ query: {} })
}

const expandedCategories = ref(new Set())

const toggleExpand = (parentSlug) => {
  if (expandedCategories.value.has(parentSlug)) {
    expandedCategories.value.delete(parentSlug)
  } else {
    expandedCategories.value.add(parentSlug)
  }
  // force la réactivité du Set
  expandedCategories.value = new Set(expandedCategories.value)
}

const isParentActive = (parent) => parent.children?.some((c) => c.slug === selectedCategory.value)

</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-primary mb-6">Catalogue équipements médicaux</h1>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Sidebar filtres -->
      <aside class="lg:col-span-1 space-y-6">
        <div>
          <div class="flex items-center justify-between mb-2">
            <h3 class="font-semibold text-sm">Disponibilité</h3>
            <button
              v-if="selectedCategory || selectedType"
              class="text-xs text-primary hover:underline"
              @click="clearFilters"
            >
              Réinitialiser
            </button>
          </div>
          <div class="space-y-1">
            <button
              v-for="opt in [
                { label: 'Vente', value: 'vente' },
                { label: 'Location', value: 'location' },
                { label: 'Vente et location', value: 'les_deux' },
              ]"
              :key="opt.value"
              class="block w-full text-left text-sm px-2 py-1.5 rounded"
              :class="selectedType === opt.value
                ? 'bg-primary/10 text-primary font-medium'
                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
              @click="applyType(opt.value)"
            >
              {{ opt.label }}
            </button>
          </div>
        </div>

        <div>
  <h3 class="font-semibold text-sm mb-2">Catégories</h3>

  <button
    class="block w-full text-left text-sm px-2 py-1.5 rounded mb-1"
    :class="!selectedCategory
      ? 'bg-primary/10 text-primary font-medium'
      : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
    @click="applyCategory(null); expandedCategories = new Set()"
  >
    Tout
  </button>

  <div class="space-y-0.5">
    <div v-for="parent in categoriesTree" :key="parent.slug">
      <button
        class="w-full flex items-center justify-between text-left text-sm px-2 py-1.5 rounded"
        :class="isParentActive(parent)
          ? 'text-primary font-medium'
          : 'text-gray-700 dark:text-gray-200'"
        @click="toggleExpand(parent.slug)"
      >
        <span>{{ parent.nom }}</span>
        <UIcon
          name="i-lucide-chevron-down"
          class="w-4 h-4 transition-transform"
          :class="{ 'rotate-180': expandedCategories.has(parent.slug) }"
        />
      </button>

      <div
        v-if="expandedCategories.has(parent.slug)"
        class="ml-3 mt-0.5 space-y-0.5 border-l pl-3"
      >
        <button
          v-for="child in parent.children"
          :key="child.slug"
          class="block w-full text-left text-sm px-2 py-1 rounded"
          :class="selectedCategory === child.slug
            ? 'bg-primary/10 text-primary font-medium'
            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
          @click="applyCategory(child.slug)"
        >
          {{ child.nom }}
        </button>
      </div>
    </div>
  </div>
</div>
      </aside>

      <!-- Liste produits -->
      <div class="lg:col-span-3">
        <div v-if="pending" class="text-center text-gray-500">Chargement...</div>

        <div v-else-if="products?.data?.length" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
          <UCard v-for="product in products.data" :key="product.slug" class="hover:shadow-lg transition-shadow">
            <template #header>
              <img
                v-if="product.images?.length"
                :src="product.images[0].url"
                :alt="product.nom"
                class="w-full h-40 object-cover rounded-t"
              />
              <div v-else class="w-full h-40 bg-gray-100 dark:bg-gray-800 rounded-t flex items-center justify-center text-gray-400">
                Pas de photo
              </div>
              <h3 class="font-semibold text-lg mt-2">{{ product.nom }}</h3>
            </template>

            <p class="text-sm text-gray-500 line-clamp-2">{{ product.description }}</p>

            <div class="flex items-center gap-2 mt-3">
              <UBadge
                :color="product.type_disponibilite === 'les_deux' ? 'primary' : 'neutral'"
                variant="subtle"
              >
                {{ product.type_disponibilite === 'les_deux' ? 'Vente & Location' : product.type_disponibilite }}
              </UBadge>
            </div>

            <template #footer>
              <div class="flex items-center justify-between">
                <span v-if="product.prix_vente" class="font-bold text-primary">
                  {{ Number(product.prix_vente).toLocaleString('fr-FR') }} FCFA
                </span>
                <UButton :to="`/produits/${product.slug}`" size="sm" variant="soft">
                  Voir détails
                </UButton>
              </div>
            </template>
          </UCard>
        </div>

        <p v-else class="text-center text-gray-500">Aucun produit ne correspond à ces filtres.</p>
      </div>
    </div>
  </div>
</template>