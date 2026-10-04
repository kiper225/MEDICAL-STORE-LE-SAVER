<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard' })

const { getAllProducts, deleteProduct, updateProduct } = useProducts()
const toast = useToast()

const { data: products, refresh } = await useAsyncData('admin-products', () => getAllProducts())

const handleDeactivate = async (slug) => {
  try {
    await deleteProduct(slug)
    toast.add({ title: 'Produit désactivé', color: 'success' })
    refresh()
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  }
}

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
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-6">Tous les produits</h1>
    <div class="flex items-center justify-between mb-6">
      <UButton :to="`/dashboard/vendeur/produits/nouveau`" icon="i-lucide-plus">
        Ajouter un produit
      </UButton>
    </div>
    <UCard>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500 border-b">
            <th class="pb-2">Image</th>
            <th class="pb-2">Nom</th>
            <th class="pb-2">Référence</th>
            <th class="pb-2">Prix vente</th>
            <th class="pb-2">Statut</th>
            <th class="pb-2 text-center">Vendeur</th>
            <th class="pb-2 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products?.data" :key="product.slug" class="border-b last:border-0">
            <td class="py-3">
              <img
                v-if="product.images?.[0]?.url"
                :src="product.images[0].url"
                alt="Produit"
                class="w-15 h-15 object-cover rounded"
              />
              <span v-else class="text-gray-400">—</span>
            </td>
            <td class="py-3">{{ product.nom }}</td>
            <td class="py-3">{{ product.reference }}</td>
            <td class="py-3">
              {{ product.prix_vente ? Number(product.prix_vente).toLocaleString('fr-FR') + ' FCFA' : '—' }}
            </td>
            <td class="py-3">
              <UBadge :color="product.statut === 'actif' ? 'success' : 'neutral'" variant="subtle" size="xs">
                {{ product.statut }}
              </UBadge>
            </td>
            <td class="py-3 text-center">{{ product.vendor?.nom }}</td>
            <td class="py-3 text-center space-x-2">
              <UButton size="xs" variant="soft" color="primary" :to="`/dashboard/vendeur/produits/${product.slug}/modifier`">
                Modifier
              </UButton>
              <UButton size="xs" variant="soft" :to="`/produits/${product.slug}`">Voir</UButton>
              <UButton
                v-if="product.statut === 'actif'"
                size="xs" variant="soft" color="error"
                @click="handleDeactivate(product.slug)"
              >
                Désactiver
              </UButton>
              <UButton
                v-else
                size="xs" variant="soft" color="success"
                @click="handleReactivate(product.slug)"
              >
                Réactiver
              </UButton>
              <UButton 
                size="xs" variant="soft" color="error" 
                @click="handleDelete(product.slug)"
                >
                Supprimer
              </UButton>
            </td>
          </tr>
        </tbody>
      </table>
    </UCard>
  </div>
</template>