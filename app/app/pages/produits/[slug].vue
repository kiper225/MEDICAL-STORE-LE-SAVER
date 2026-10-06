<script setup>
const route = useRoute()
const { getProduct, getProducts } = useProducts()
const { createOrder } = useOrders()
const { createRental } = useRentals()
const cartStore = useCartStore()
const authStore = useAuthStore()
const toast = useToast()
const router = useRouter()

const { data: product, pending, error } = await useAsyncData(
   `product-${route.params.slug}`,
  () => getProduct(route.params.slug)
)

const { data: suggestions } = await useAsyncData(
  `suggestions-${route.params.slug}`,
  async () => {
    const res = await getProducts({ category_slug: product.value?.category_slug })
    return res.data?.filter((p) => p.slug !== route.params.slug).slice(0, 5) || []
  }
)

const activeImageIndex = ref(0)
const quantite = ref(1)

const showRentalModal = ref(false)
const dateDebut = ref('')
const dateFin = ref('')
const showPaymentModal = ref(false)
const pendingOrderId = ref(null)
const submitting = ref(false)

const requireAuth = () => {
  if (!authStore.user) {
    navigateTo(`/login?redirect=/produits/${route.params.slug}`)
    return false
  }
  return true
}

const handleAjouterPanier = () => {
  cartStore.addItem(product.value, quantite.value)
  toast.add({ title: 'Ajouté au panier', color: 'success' })
}

const handleAcheterMaintenant = async () => {
  if (!requireAuth()) return
  submitting.value = true
  try {
    const order = await createOrder({
      items: [{ product_slug: product.value.slug, quantite: quantite.value }],
      mode_paiement: 'mobile_money',
    })
    pendingOrderId.value = order.id
    showPaymentModal.value = true
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  } finally {
    submitting.value = false
  }
}

const handleLouer = () => {
  if (!requireAuth()) return
  showRentalModal.value = true
}

const confirmLocation = async () => {
  submitting.value = true
  try {
    await createRental({
      product_slug: product.value.slug,
      date_debut: dateDebut.value,
      date_fin: dateFin.value,
    })
    toast.add({ title: 'Location réservée avec succès', color: 'success' })
    showRentalModal.value = false
    navigateTo('/dashboard/client')
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  } finally {
    submitting.value = false
  }
}

const handlePaymentSuccess = () => navigateTo('/dashboard/client')


const { getReviews, submitReview } = useReviews()

const { data: reviews, refresh: refreshReviews } = await useAsyncData(
  `reviews-${route.params.slug}`,
  () => getReviews(route.params.slug)
)

const activeTab = ref('description')
const selectedColor = ref(null)

const newReviewNote = ref(5)
const newReviewComment = ref('')
const submittingReview = ref(false)

const handleSubmitReview = async () => {
  if (!requireAuth()) return
  submittingReview.value = true
  try {
    await submitReview(route.params.slug, {
      note: newReviewNote.value,
      commentaire: newReviewComment.value,
    })
    toast.add({ title: 'Avis publié', color: 'success' })
    newReviewComment.value = ''
    refreshReviews()
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  } finally {
    submittingReview.value = false
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <div v-if="pending" class="text-center text-gray-500 py-12">Chargement...</div>
    <UAlert v-else-if="error" color="error" variant="soft" title="Produit introuvable" />

    <div v-else-if="product">
      <!-- Fil d'Ariane -->
      <nav class="text-sm text-gray-500 mb-6 flex flex-wrap items-center gap-1">
        <NuxtLink to="/" class="hover:text-primary">Accueil</NuxtLink>
        <span>»</span>
        <NuxtLink to="/produits" class="hover:text-primary">Tous les produits</NuxtLink>
        <span>»</span>
        <NuxtLink :to="`/produits?category_slug=${product.category_slug}`" class="hover:text-primary">
          {{ product.category?.nom }}
        </NuxtLink>
        <span>»</span>
        <span class="text-gray-700 dark:text-gray-300">{{ product.nom }}</span>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Colonne principale -->
        <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Galerie -->
          <div>
            <div class="relative bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden aspect-square">
              <img
                v-if="product.images?.length"
                :src="product.images[activeImageIndex]?.url"
                :alt="product.nom"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                Pas de photo
              </div>
            </div>

            <div v-if="product.images?.length > 1" class="flex gap-2 mt-3">
              <button
                v-for="(img, i) in product.images"
                :key="img.id"
                class="w-16 h-16 rounded-lg overflow-hidden border-2"
                :class="i === activeImageIndex ? 'border-primary' : 'border-gray-200 dark:border-gray-800'"
                @click="activeImageIndex = i"
              >
                <img :src="img.url" class="w-full h-full object-cover" />
              </button>
            </div>
          </div>

          <!-- Infos produit -->
          <div>
            <h1 class="text-2xl font-bold">{{ product.nom }}</h1>

            <div class="flex items-baseline gap-2 mt-3">
              <template v-if="product.en_promo">
                <span class="text-2xl font-bold text-error">{{ Number(product.prix_promo).toLocaleString('fr-FR') }} FCFA</span>
                <span class="text-sm text-gray-400 line-through">{{ Number(product.prix_vente).toLocaleString('fr-FR') }} FCFA</span>
                <UBadge color="error" size="sm">-{{ product.pourcentage_reduction }}%</UBadge>
              </template>
              <span v-else-if="product.prix_vente" class="text-2xl font-bold text-primary">
                {{ Number(product.prix_vente).toLocaleString('fr-FR') }} FCFA
              </span>
            </div>

            <ul class="mt-4 space-y-1.5 text-sm text-gray-600 dark:text-gray-300">
              <li v-if="product.description">• {{ product.description }}</li>
              <li v-if="product.category">• Catégorie : {{ product.category.nom }}</li>
              <li v-if="product.reference">• Référence : {{ product.reference }}</li>
            </ul>

            <div class="flex flex-wrap gap-2 mt-4">
              <UBadge v-if="product.necessite_installation" color="warning" variant="subtle">
                Installation requise
              </UBadge>
              <UBadge v-if="product.necessite_certification" color="info" variant="subtle">
                Certification requise
              </UBadge>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-800 mt-4 pt-4 space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="font-medium">Poids</span>
                <span class="text-gray-500">{{ product.poids ? `${product.poids} kg` : '—' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium">Dimensions</span>
                <span class="text-gray-500">{{ product.dimensions || '—' }}</span>
              </div>
            </div>

            <!-- Sélecteur quantité + actions -->
            <div v-if="product.type_disponibilite !== 'location'" class="flex items-center gap-3 mt-6">
              <div class="flex items-center border border-gray-200 dark:border-gray-800 rounded-lg">
                <button class="w-9 h-9 flex items-center justify-center text-gray-500" @click="quantite = Math.max(1, quantite - 1)">−</button>
                <span class="w-10 text-center text-sm">{{ quantite }}</span>
                <button class="w-9 h-9 flex items-center justify-center text-gray-500" @click="quantite++">+</button>
              </div>

              <UButton variant="soft" color="warning" @click="handleAjouterPanier">
                Ajouter au panier
              </UButton>
              <UButton color="primary" :loading="submitting" @click="handleAcheterMaintenant">
                Achetez maintenant
              </UButton>
            </div>

            <div v-if="product.type_disponibilite !== 'vente'" class="mt-3">
              <UButton variant="outline" block @click="handleLouer">
                Louer ce produit
              </UButton>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-800 mt-6 pt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
              <p><strong>Retours</strong> sous 5 jours</p>
              <p><strong>Livraison</strong> 48H maxi</p>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-800 mt-4 pt-4 text-sm space-y-1 text-gray-600 dark:text-gray-300">
              <p v-if="product.vendor">Vendu par <strong>{{ product.vendor.nom }}</strong></p>
              <p v-if="product.reference">Référence : <span class="text-gray-400">{{ product.reference }}</span></p>
            </div>

            <div class="flex items-center gap-3 mt-4">
              <span class="text-sm text-gray-500">Partager :</span>
              <UIcon name="i-simple-icons-facebook" class="w-4 h-4 text-gray-400 hover:text-primary cursor-pointer" />
              <UIcon name="i-simple-icons-x" class="w-4 h-4 text-gray-400 hover:text-primary cursor-pointer" />
              <UIcon name="i-simple-icons-whatsapp" class="w-4 h-4 text-gray-400 hover:text-primary cursor-pointer" />
            </div>
          </div>
        </div>

        <!-- Sidebar suggestions -->
        <aside class="lg:col-span-1">
          <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Sélectionnés pour vous</h3>
          <div class="space-y-3">
            <NuxtLink
              v-for="item in suggestions"
              :key="item.slug"
              :to="`/produits/${item.slug}`"
              class="flex gap-3 items-center group"
            >
              <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden shrink-0">
                <img v-if="item.images?.length" :src="item.images[0].url" class="w-full h-full object-cover" />
              </div>
              <div>
                <p class="text-sm font-medium group-hover:text-primary line-clamp-2">{{ item.nom }}</p>
                <p v-if="item.prix_vente" class="text-sm font-bold text-primary">
                  {{ Number(item.prix_vente).toLocaleString('fr-FR') }} FCFA
                </p>
              </div>
            </NuxtLink>
          </div>
        </aside>
      </div>
    </div>

    <!-- Sélecteur de couleur -->
    <div v-if="product.couleurs?.length" class="mt-6">
      <p class="text-sm font-medium mb-2">Couleur :</p>
      <div class="flex gap-2">
        <button
          v-for="couleur in product.couleurs"
          :key="couleur"
          class="w-8 h-8 rounded-full border-2 transition-transform"
          :class="selectedColor === couleur ? 'border-primary scale-110' : 'border-gray-200 dark:border-gray-700'"
          :style="{ backgroundColor: couleur }"
          @click="selectedColor = couleur"
        />
      </div>
    </div>

    <!-- Onglets -->
    <div class="mt-10">
      <div class="flex gap-6 border-b border-gray-200 dark:border-gray-800">
        <button
          v-for="tab in ['description', 'boutique', 'avis']"
          :key="tab"
          class="pb-3 text-sm font-medium border-b-2 -mb-px"
          :class="activeTab === tab ? 'border-primary text-primary' : 'border-transparent text-gray-500'"
          @click="activeTab = tab"
        >
          {{ tab === 'description' ? 'Description' : tab === 'boutique' ? 'Boutique' : `Avis (${product.reviews_count || 0})` }}
        </button>
      </div>

      <div class="py-6">
        <p v-if="activeTab === 'description'" class="text-gray-600 dark:text-gray-300 leading-relaxed">
          {{ product.description || 'Aucune description disponible.' }}
        </p>

        <div v-else-if="activeTab === 'boutique'" class="text-sm text-gray-600 dark:text-gray-300">
          <p class="font-medium text-base mb-1">{{ product.vendor?.nom }}</p>
          <p>{{ product.vendor?.telephone || 'Contact non renseigné' }}</p>
        </div>

        <div v-else-if="activeTab === 'avis'" class="space-y-6">
          <div class="flex items-center gap-3">
            <span class="text-3xl font-bold">{{ Number(product.reviews_avg_note || 0).toFixed(1) }}</span>
            <div>
              <div class="flex text-warning">
                <UIcon
                  v-for="star in 5" :key="star"
                  :name="star <= Math.round(product.reviews_avg_note || 0) ? 'i-lucide-star' : 'i-lucide-star'"
                  class="w-4 h-4"
                  :class="star <= Math.round(product.reviews_avg_note || 0) ? 'text-warning' : 'text-gray-300'"
                />
              </div>
              <p class="text-xs text-gray-500">{{ product.reviews_count || 0 }} avis</p>
            </div>
          </div>

          <div v-if="authStore.user" class="border-t border-gray-200 dark:border-gray-800 pt-4">
            <p class="text-sm font-medium mb-2">Laisser un avis</p>
            <div class="flex gap-1 mb-2">
              <button v-for="star in 5" :key="star" @click="newReviewNote = star">
                <UIcon
                  name="i-lucide-star"
                  class="w-5 h-5"
                  :class="star <= newReviewNote ? 'text-warning' : 'text-gray-300'"
                />
              </button>
            </div>
            <UTextarea v-model="newReviewComment" placeholder="Votre commentaire (optionnel)" class="w-full" />
            <UButton size="sm" class="mt-2" :loading="submittingReview" @click="handleSubmitReview">
              Publier
            </UButton>
          </div>

          <div class="space-y-4 border-t border-gray-200 dark:border-gray-800 pt-4">
            <div v-for="review in reviews?.data" :key="review.id">
              <div class="flex items-center gap-2">
                <span class="font-medium text-sm">{{ review.user?.nom }}</span>
                <div class="flex text-warning">
                  <UIcon
                    v-for="star in 5" :key="star"
                    name="i-lucide-star"
                    class="w-3.5 h-3.5"
                    :class="star <= review.note ? 'text-warning' : 'text-gray-300'"
                  />
                </div>
              </div>
              <p v-if="review.commentaire" class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                {{ review.commentaire }}
              </p>
            </div>
            <p v-if="!reviews?.data?.length" class="text-sm text-gray-500">Il n'y a pas encore d'avis.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Produits similaires -->
    <div v-if="suggestions?.length" class="mt-10">
      <h2 class="text-lg font-bold mb-4">Produits similaires</h2>
      <div class="flex gap-4 overflow-x-auto pb-2">
        <NuxtLink
          v-for="item in suggestions"
          :key="item.id"
          :to="`/produits/${item.slug}`"
          class="shrink-0 w-48 group"
        >
          <div class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden mb-2">
            <img v-if="item.images?.length" :src="item.images[0].url" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
          </div>
          <p class="text-sm font-medium line-clamp-2 group-hover:text-primary">{{ item.nom }}</p>
          <p v-if="item.prix_vente" class="text-primary font-bold text-sm">
            {{ Number(item.prix_vente).toLocaleString('fr-FR') }} FCFA
          </p>
        </NuxtLink>
      </div>
    </div>

    <!-- Modals -->
    <UModal v-model="showRentalModal">
      <template #content>
        <UCard>
          <template #header>
            <h3 class="font-semibold">Réserver ce produit</h3>
          </template>
          <div class="space-y-4">
            <UFormField label="Date de début">
              <UInput v-model="dateDebut" type="date" class="w-full" />
            </UFormField>
            <UFormField label="Date de fin">
              <UInput v-model="dateFin" type="date" class="w-full" />
            </UFormField>
          </div>
          <template #footer>
            <UButton block :loading="submitting" @click="confirmLocation">
              Confirmer la réservation
            </UButton>
          </template>
        </UCard>
      </template>
    </UModal>

    <PaymentModal
      v-model="showPaymentModal"
      :order-id="pendingOrderId"
      @success="handlePaymentSuccess"
    />
  </div>
</template>