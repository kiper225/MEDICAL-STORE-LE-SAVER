<script setup>
const props = defineProps({
  categoriesTree: { type: Array, default: () => [] },
})
const emit = defineEmits(['close'])

const activeParent = ref(props.categoriesTree?.[0]?.slug || null)

const activeCategory = computed(() =>
  props.categoriesTree.find((c) => c.slug === activeParent.value)
)
</script>

<template>
  <div
    class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-xl z-40 flex"
    style="width: 720px; max-width: 90vw;"
    @mouseleave="emit('close')"
  >
    <!-- Colonne gauche : catégories parentes -->
    <div class="w-56 border-r border-gray-200 dark:border-gray-800 py-2 shrink-0">
      <button
        v-for="parent in categoriesTree"
        :key="parent.slug"
        class="w-full flex items-center justify-between px-4 py-2.5 text-sm text-left"
        :class="activeParent === parent.slug
          ? 'bg-primary/10 text-primary font-medium'
          : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800'"
        @mouseenter="activeParent = parent.slug"
      >
        <span>{{ parent.nom }}</span>
        <UIcon name="i-lucide-chevron-right" class="w-4 h-4" />
      </button>
    </div>

    <!-- Panneau droit : sous-catégories de la catégorie survolée -->
    <div class="flex-1 p-5">
      <template v-if="activeCategory">
        <h3 class="font-semibold text-sm mb-3">{{ activeCategory.nom }}</h3>
        <div class="grid grid-cols-2 gap-x-6 gap-y-2">
          <NuxtLink
            v-for="child in activeCategory.children"
            :key="child.slug"
            :to="`/produits?category_slug=${child.slug}`"
            class="text-sm text-gray-600 dark:text-gray-300 hover:text-primary"
            @click="emit('close')"
          >
            {{ child.nom }}
          </NuxtLink>
        </div>
        <NuxtLink
          :to="`/produits?category_slug=${activeCategory.children?.[0]?.slug}`"
          class="inline-block mt-4 text-sm font-medium text-primary hover:underline"
          @click="emit('close')"
        >
          Voir tout {{ activeCategory.nom.toLowerCase() }} →
        </NuxtLink>
      </template>
    </div>
  </div>
</template>