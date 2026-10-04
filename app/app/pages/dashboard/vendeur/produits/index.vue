<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard', pageTitle: 'Mon espace' })

const authStore = useAuthStore()
const { getProducts, deleteProduct, updateProduct } = useProducts()
const toast = useToast()

const { data: products, refresh } = await useAsyncData('vendor-products', () =>
  getProducts({ vendor_id: authStore.user.id })
)

const handleDelete = async (slug) => {
  try {
    await deleteProduct(slug)
    toast.add({ title: 'Produit désactivé', color: 'success' })
    refresh()
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  }
}

const handleReactivate = async (slug) => {
  try {
    await updateProduct(slug, { statut: 'actif' })
    toast.add({ title: 'Produit réactivé', color: 'success' })
    refresh()
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  }
}
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Mes produits</h1>
        <UButton to="/dashboard/vendeur/produits/nouveau" icon="i-lucide-plus">
            Ajouter un produit
        </UButton>
    </div>

    <div v-if="products?.data?.length" class="space-y-3">
      <UCard v-for="product in products.data" :key="product.slug">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium">{{ product.nom }}</p>
            <p class="text-sm text-gray-500">
              {{ product.reference }} ·
              <UBadge size="xs" :color="product.statut === 'actif' ? 'success' : 'neutral'" variant="subtle">
                {{ product.statut }}
              </UBadge>
            </p>
          </div>
          <div class="flex gap-2">
            <UButton size="sm" variant="soft" :to="`/produits/${product.slug}`">Voir</UButton>
            <UButton
              v-if="product.statut === 'actif'"
              size="sm" variant="soft" color="error"
              @click="handleDelete(product.slug)"
            >
              Désactiver
            </UButton>
            <UButton
              v-else
              size="sm" variant="soft" color="success"
              @click="handleReactivate(product.slug)"
            >
              Réactiver
            </UButton>
          </div>
        </div>
      </UCard>
    </div>
    <p v-else class="text-gray-500">Aucun produit pour le moment.</p>
  </div>
</template>