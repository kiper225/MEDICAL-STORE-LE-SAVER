<script setup>
defineProps({
  title: { type: String, required: true },
  seeMoreLink: { type: String, default: '/produits' },
  products: { type: Array, default: () => [] },
})
</script>

<template>
  <div v-if="products.length" class="mt-8">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-bold">{{ title }}</h2>
      <NuxtLink :to="seeMoreLink" class="text-sm text-primary hover:underline">Voir plus</NuxtLink>
    </div>
    <div class="flex gap-4 overflow-x-auto pb-2">
      <NuxtLink
        v-for="product in products"
        :key="product.id"
        :to="`/produits/${product.slug}`"
        class="shrink-0 w-44 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden group"
      >
        <div class="aspect-square bg-gray-100 dark:bg-gray-800 overflow-hidden">
          <img
            v-if="product.images?.length"
            :src="product.images[0].url"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
            Pas de photo
          </div>
        </div>
        <div class="p-3">
          <p class="text-sm font-medium line-clamp-2 group-hover:text-primary">{{ product.nom }}</p>
          <p v-if="product.prix_vente" class="text-primary font-bold text-sm mt-1">
            {{ Number(product.prix_vente).toLocaleString('fr-FR') }} FCFA
          </p>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>