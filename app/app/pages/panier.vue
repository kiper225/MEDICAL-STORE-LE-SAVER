<script setup>
const cartStore = useCartStore()
const { createOrder } = useOrders()
const { getProducts } = useProducts()
const authStore = useAuthStore()
const toast = useToast()
const router = useRouter()

const FRAIS_LIVRAISON = 1000
const promoCode = ref('')
const modePaiement = ref('cod')

const showPaymentModal = ref(false)
const pendingOrderId = ref(null)
const submitting = ref(false)

const totalAvecLivraison = computed(() => cartStore.totalPrice + (cartStore.items.length ? FRAIS_LIVRAISON : 0))

const handleCheckout = async () => {
  if (!authStore.user) {
    router.push('/login?redirect=/panier')
    return
  }

  submitting.value = true
  try {
    const order = await createOrder({
      items: cartStore.items.map((i) => ({ product_id: i.product.id, quantite: i.quantite })),
      mode_paiement: modePaiement.value,
    })

    if (modePaiement.value === 'mobile_money') {
      pendingOrderId.value = order.id
      showPaymentModal.value = true
    } else {
      cartStore.clear()
      toast.add({ title: 'Commande confirmée', description: 'Paiement à la livraison', color: 'success' })
      router.push('/dashboard/client')
    }
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  } finally {
    submitting.value = false
  }
}

const handlePaymentSuccess = () => {
  cartStore.clear()
  router.push('/dashboard/client')
}

const { data: recentProducts } = await useAsyncData('recent-products', async () => {
  const res = await getProducts()
  const cartIds = cartStore.items.map((i) => i.product.id)
  return res.data?.filter((p) => !cartIds.includes(p.id)).slice(0, 4) || []
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <ClientOnly>
      <h1 class="text-2xl font-bold mb-6">Mon panier</h1>

      <div v-if="cartStore.items.length" class="mb-6">
        <p class="text-sm text-gray-500">
          Vous avez {{ cartStore.items.length }} article{{ cartStore.items.length > 1 ? 's' : '' }} dans votre panier.
        </p>
      </div>
      
      <div v-if="cartStore.items.length" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tableau produits -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-5">
          <table class="w-full">
            <thead>
              <tr class="text-left text-xs uppercase text-gray-400 border-b border-gray-200 dark:border-gray-800">
                <th class="pb-3 w-8"></th>
                <th class="pb-3">Produit</th>
                <th class="pb-3">Prix</th>
                <th class="pb-3">Quantité</th>
                <th class="pb-3 text-right">Sous-total</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in cartStore.items"
                :key="item.product.id"
                class="border-b border-gray-100 dark:border-gray-800 last:border-0"
              >
                <td class="py-4">
                  <button class="text-gray-400 hover:text-error" @click="cartStore.removeItem(item.product.id)">
                    <UIcon name="i-lucide-x" class="w-4 h-4" />
                  </button>
                </td>
                <td class="py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded overflow-hidden shrink-0">
                      <img v-if="item.product.images?.length" :src="item.product.images[0].url" class="w-full h-full object-cover" />
                    </div>
                    <div>
                      <NuxtLink :to="`/produits/${item.product.slug}`" class="text-sm font-medium hover:text-primary">
                        {{ item.product.nom }}
                      </NuxtLink>
                      <p class="text-xs text-gray-400 mt-0.5">Vendu par : {{ item.product.vendor?.nom || '—' }}</p>
                    </div>
                  </div>
                </td>
                <td class="py-4 text-sm">{{ Number(item.product.prix_vente).toLocaleString('fr-FR') }} FCFA</td>
                <td class="py-4">
                  <div class="flex items-center border border-gray-200 dark:border-gray-800 rounded-lg w-fit">
                    <button class="w-8 h-8 text-gray-500" @click="cartStore.updateQuantity(item.product.id, item.quantite - 1)">−</button>
                    <span class="w-8 text-center text-sm">{{ item.quantite }}</span>
                    <button class="w-8 h-8 text-gray-500" @click="cartStore.updateQuantity(item.product.id, item.quantite + 1)">+</button>
                  </div>
                </td>
                <td class="py-4 text-right font-semibold text-primary">
                  {{ (Number(item.product.prix_vente) * item.quantite).toLocaleString('fr-FR') }} FCFA
                </td>
              </tr>
            </tbody>
          </table>

          <div class="flex flex-col sm:flex-row gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
            <UInput v-model="promoCode" placeholder="Code promo" class="flex-1" />
            <UButton color="warning">Appliquer le code promo</UButton>
          </div>
        </div>

        <!-- Total panier -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-5">
            <h2 class="font-bold text-lg mb-4">Total panier</h2>

            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">Sous-total</span>
                <span class="font-medium">{{ cartStore.totalPrice.toLocaleString('fr-FR') }} FCFA</span>
              </div>
              <div class="flex justify-between border-b border-gray-200 dark:border-gray-800 pb-3">
                <span class="text-gray-500">Frais livraison</span>
                <span class="font-medium text-primary">{{ FRAIS_LIVRAISON.toLocaleString('fr-FR') }} FCFA</span>
              </div>
              <div class="flex justify-between items-center text-lg font-bold pt-1">
                <span>Total</span>
                <span class="text-primary">{{ totalAvecLivraison.toLocaleString('fr-FR') }} FCFA</span>
              </div>
            </div>

            <div class="mt-4 space-y-2">
              <label class="flex items-center gap-2 text-sm border border-gray-200 dark:border-gray-800 rounded-lg p-3 cursor-pointer" :class="{ 'border-primary bg-primary/5': modePaiement === 'cod' }">
                <input type="radio" v-model="modePaiement" value="cod" class="accent-primary" />
                Paiement à la livraison
              </label>
              <label class="flex items-center gap-2 text-sm border border-gray-200 dark:border-gray-800 rounded-lg p-3 cursor-pointer" :class="{ 'border-primary bg-primary/5': modePaiement === 'mobile_money' }">
                <input type="radio" v-model="modePaiement" value="mobile_money" class="accent-primary" />
                Mobile Money
              </label>
            </div>

            <UButton block size="lg" class="mt-5" :loading="submitting" @click="handleCheckout">
              Valider la commande
            </UButton>

            <UButton block variant="soft" class="mt-2" to="/produits">
              Continuer mes achats
            </UButton>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-16 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg">
        <UIcon name="i-lucide-shopping-cart" class="w-12 h-12 mx-auto text-gray-300 mb-3" />
        <p class="text-gray-500">Votre panier est vide.</p>
        <UButton to="/produits" class="mt-4" variant="soft">Voir le catalogue</UButton>
      </div>

      <template #fallback>
        <div class="text-center py-16">
          <div class="w-8 h-8 mx-auto border-2 border-primary border-t-transparent rounded-full animate-spin" />
        </div>
      </template>
    </ClientOnly>
    <!-- Vus récemment -->
    <div v-if="recentProducts?.length" class="mt-10">
      <h2 class="text-lg font-bold mb-4">Vus récemment</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <NuxtLink
          v-for="product in recentProducts"
          :key="product.id"
          :to="`/produits/${product.slug}`"
          class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden group"
        >
          <div class="aspect-square bg-gray-100 dark:bg-gray-800 overflow-hidden">
            <img v-if="product.images?.length" :src="product.images[0].url" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
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

    <PaymentModal
      v-model="showPaymentModal"
      :order-id="pendingOrderId"
      @success="handlePaymentSuccess"
    />
  </div>
</template>