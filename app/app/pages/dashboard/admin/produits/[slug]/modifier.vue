<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard', pageTitle: 'Modifier le produit' })

const route = useRoute()
const router = useRouter()
const { getProduct } = useProducts()

const { data: product, pending, error } = await useAsyncData(
  `edit-admin-${route.params.slug}`,
  () => getProduct(route.params.slug)
)
</script>

<template>
  <div class="max-w-2xl mx-auto px-4 py-8">
    <div class="flex items-center gap-3 mb-6">
      <UButton icon="i-lucide-arrow-left" variant="ghost" color="neutral" @click="router.back()" />
      <h1 class="text-2xl font-bold text-primary">Modifier le produit</h1>
    </div>

    <div v-if="pending" class="text-center text-gray-500 py-8">Chargement...</div>

    <UAlert
      v-else-if="error"
      color="error"
      variant="soft"
      title="Produit introuvable"
    />

    <ProductForm v-else-if="product" :initial-product="product" />
  </div>
</template>