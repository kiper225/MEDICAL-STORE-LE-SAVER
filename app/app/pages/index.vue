<script setup>
const { getCategories } = useCategories()
const { getProducts } = useProducts()

const { data: categoriesTree } = await useAsyncData('home-categories', () => getCategories())
const { data: products } = await useAsyncData('home-products', () => getProducts())

const expandedCategories = ref(new Set())
const toggleExpand = (slug) => {
  expandedCategories.value.has(slug) ? expandedCategories.value.delete(slug) : expandedCategories.value.add(slug)
  expandedCategories.value = new Set(expandedCategories.value)
}

const hoveredCategory = ref(null)

// Un rayon de produits par catégorie parente (jusqu'à 4 rayons)
const { data: rails } = await useAsyncData('home-rails', async () => {
  const cats = categoriesTree.value || []
  const results = []
  for (const parent of cats.slice(0, 4)) {
    const firstChild = parent.children?.[0]
    if (!firstChild) continue
    const res = await getProducts({ category_slug: firstChild.slug, per_page: 8 })
    if (res.data?.length) {
      results.push({ title: parent.nom, slug: firstChild.slug, products: res.data })
    }
  }
  return results
})

const slides = computed(() => {
  const cats = categoriesTree.value || []
  const bgClasses = [
    'bg-gradient-to-br from-primary to-primary-700',
    'bg-gradient-to-br from-blue-600 to-blue-900',
    'bg-gradient-to-br from-slate-700 to-slate-900',
    'bg-gradient-to-br from-primary-600 to-primary-900',
    'bg-gradient-to-br from-indigo-600 to-indigo-900',
    'bg-gradient-to-br from-cyan-700 to-cyan-950',
    'bg-gradient-to-br from-teal-700 to-teal-950',
    'bg-gradient-to-br from-blue-800 to-slate-900',
  ]

  const bgImages = [
    'bg-[url("/img/carrousel/bloc-operatoire1.jpg")]',
    'bg-[url("/img/carrousel/imagerie2.jpg")]',
    'bg-[url("/img/carrousel/gros-plan-main.jpg")]',
    'bg-[url("/img/carrousel/fauteuil-roulant.jpg")]',
    'bg-[url("/img/carrousel/full-electric-hospital-bed-m100-1.2.jpg")]',
    'bg-[url("/img/carrousel/equipement1.jpg")]',
    'bg-[url("/img/carrousel/istockphoto-913784822-612x612.jpg")]',
    'bg-[url("/img/carrousel/10dfcc6793201217163b70bc71f62b0d9558843d091973394.png")]',
  ]

  return [
    {
      eyebrow: 'ÉQUIPEMENTS CERTIFIÉS',
      title: 'Le matériel médical près de chez vous',
      subtitle: 'Achat, location et installation à domicile',
      cta: 'Découvrir le catalogue',
      link: '/produits',
      bgClass: bgClasses[0],
      backgroundImage: bgImages[0]
    },
    {
      eyebrow: 'SERVICE',
      title: 'Installation à domicile incluse',
      subtitle: 'Un technicien assigné sous 48h pour vos équipements',
      cta: 'En savoir plus',
      link: '/produits',
      bgClass: bgClasses[1],
      backgroundImage: bgImages[1]
    },
    {
      eyebrow: 'FLEXIBILITÉ',
      title: 'Location jour, semaine ou mois',
      subtitle: 'Payez uniquement pour la durée dont vous avez besoin',
      cta: 'Voir les tarifs',
      link: '/produits?type_disponibilite=location',
      bgClass: bgClasses[2],
      backgroundImage: bgImages[2]
    },
    ...cats.slice(0, 5).map((cat, i) => ({
      eyebrow: 'CATÉGORIE',
      title: cat.nom,
      subtitle: `Découvrez notre sélection ${cat.nom.toLowerCase()}`,
      cta: 'Voir les produits',
      link: `/produits?category_slug=${cat.slug}`,
      bgClass: bgClasses[(i + 3) % bgClasses.length],
      backgroundImage: bgImages[(i + 3) % bgImages.length]
    })),
  ].slice(0, 8)
})

const faqs = [
  {
    q: 'Quel est le délai de livraison ?',
    a: 'Livraison sous 48h maximum à Abidjan. Pour l\'intérieur du pays, comptez 3 à 5 jours ouvrables selon la ville.',
  },
  {
    q: 'Comment fonctionne la location d\'équipement ?',
    a: 'Choisissez vos dates sur la fiche produit, une caution est demandée, et un technicien assure l\'installation si l\'équipement le nécessite.',
  },
  {
    q: 'Quels modes de paiement acceptez-vous ?',
    a: 'Mobile Money (Orange Money, MTN, Moov, Wave) et paiement à la livraison selon les produits.',
  },
  {
    q: 'Les équipements sont-ils certifiés ?',
    a: 'Les produits marqués "Certification requise" sont conformes aux normes en vigueur ; vérifiez le badge sur chaque fiche produit.',
  },
]
const openFaq = ref(null)
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Bannière + promos -->
    <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4 h-120">
      <div class="lg:col-span-3">
        <AppCarousel :slides="slides" />
      </div>
    </div>
    
    <!-- Raccourcis rapides -->
    <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
      <NuxtLink to="/produits" class="bg-primary/10 rounded-lg p-5 flex flex-col justify-center hover:bg-primary/15 transition-colors">
        <UIcon name="i-lucide-layout-grid" class="w-7 h-7 text-primary mb-2" />
        <p class="font-semibold text-sm">Tout le catalogue</p>
      </NuxtLink>
      <NuxtLink to="/produits?type_disponibilite=location" class="bg-primary/10 rounded-lg p-5 flex flex-col justify-center hover:bg-primary/15 transition-colors">
        <UIcon name="i-lucide-calendar-clock" class="w-7 h-7 text-primary mb-2" />
        <p class="font-semibold text-sm">Location flexible</p>
      </NuxtLink>
      <div class="bg-primary/10 rounded-lg p-5 flex flex-col justify-center">
        <UIcon name="i-lucide-truck" class="w-7 h-7 text-primary mb-2" />
        <p class="font-semibold text-sm">Installation à domicile</p>
      </div>
      <NuxtLink to="/register?role=vendeur" class="bg-primary/10 rounded-lg p-5 flex flex-col justify-center hover:bg-primary/15 transition-colors">
        <UIcon name="i-lucide-store" class="w-7 h-7 text-primary mb-2" />
        <p class="font-semibold text-sm">Devenir vendeur</p>
      </NuxtLink>
    </div>

    <!-- Nos meilleures catégories -->
    <div class="mt-8">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold">Nos meilleures catégories</h2>
        <NuxtLink to="/produits" class="text-sm text-primary hover:underline">Voir plus</NuxtLink>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <NuxtLink
          v-for="parent in categoriesTree?.slice(0, 5)"
          :key="parent.slug"
          :to="`/produits?category_slug=${parent.children?.[0]?.slug}`"
          class="bg-white dark:bg-gray-900 border rounded-lg p-4 flex flex-col items-center text-center hover:shadow-md transition-shadow"
        >
          <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-3">
            <UIcon name="i-lucide-stethoscope" class="w-8 h-8 text-primary" />
          </div>
          <p class="text-sm font-medium">{{ parent.nom }}</p>
        </NuxtLink>
      </div>
    </div>

    <!-- Tous nos équipements -->
    <div class="mt-8">
      <div class="bg-primary text-white rounded-t-lg px-5 py-3 flex items-center justify-between">
        <h2 class="font-bold">Tous nos équipements</h2>
        <NuxtLink to="/produits" class="text-sm hover:underline">Voir plus</NuxtLink>
      </div>
      <div class="border border-t-0 border-gray-200 dark:border-gray-700 rounded-b-lg p-5 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <NuxtLink
          v-for="product in products?.data"
          :key="product.id"
          :to="`/produits/${product.slug}`"
          class="group"
        >
          <div class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden mb-2">
            <img
              v-if="product.images?.length"
              :src="product.images[0].url"
              :alt="product.nom"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
              Pas de photo
            </div>
          </div>
          <p class="text-sm font-medium line-clamp-2">{{ product.nom }}</p>
          <p v-if="product.prix_vente" class="text-primary font-bold text-sm mt-1">
            {{ Number(product.prix_vente).toLocaleString('fr-FR') }} FCFA
          </p>
        </NuxtLink>
      </div>
    </div>

    <!-- Rayons par catégorie -->
    <ProductRail
      v-for="rail in rails"
      :key="rail.slug"
      :title="rail.title"
      :see-more-link="`/produits?categorie=${rail.slug}`"
      :products="rail.products"
    />

    <!-- Services -->
    <div class="mt-10 grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-5 flex items-center gap-3">
        <UIcon name="i-lucide-truck" class="w-9 h-9 text-primary shrink-0" />
        <div>
          <p class="font-semibold text-sm">Livraison rapide</p>
          <p class="text-xs text-gray-500">48H maxi à Abidjan</p>
        </div>
      </div>
      <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-5 flex items-center gap-3">
        <UIcon name="i-lucide-wallet" class="w-9 h-9 text-primary shrink-0" />
        <div>
          <p class="font-semibold text-sm">Paiement facile</p>
          <p class="text-xs text-gray-500">Mobile Money & CB</p>
        </div>
      </div>
      <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-5 flex items-center gap-3">
        <UIcon name="i-lucide-headset" class="w-9 h-9 text-primary shrink-0" />
        <div>
          <p class="font-semibold text-sm">Support 24/7</p>
          <p class="text-xs text-gray-500">Assistance illimitée</p>
        </div>
      </div>
      <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-5 flex items-center gap-3">
        <UIcon name="i-lucide-shield-check" class="w-9 h-9 text-primary shrink-0" />
        <div>
          <p class="font-semibold text-sm">100% fiable</p>
          <p class="text-xs text-gray-500">Produits certifiés conformes</p>
        </div>
      </div>
    </div>

    <!-- FAQ -->
    <div class="mt-10 max-w-3xl">
      <h2 class="text-lg font-bold mb-4">Questions fréquentes</h2>
      <div class="divide-y divide-gray-200 dark:divide-gray-800 border-t border-b border-gray-200 dark:border-gray-800">
        <div v-for="(faq, i) in faqs" :key="i">
          <button
            class="w-full flex items-center justify-between py-3 text-left text-sm font-medium"
            @click="openFaq = openFaq === i ? null : i"
          >
            {{ faq.q }}
            <UIcon name="i-lucide-chevron-down" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': openFaq === i }" />
          </button>
          <p v-if="openFaq === i" class="pb-3 text-sm text-gray-600 dark:text-gray-300">
            {{ faq.a }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
  .fade-enter-active, .fade-leave-active {
    transition: opacity 0.15s ease;
  }
  .fade-enter-from, .fade-leave-to {
    opacity: 0;
  }
</style>