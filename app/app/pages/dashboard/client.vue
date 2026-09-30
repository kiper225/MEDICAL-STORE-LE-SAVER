<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard', pageTitle: 'Mon espace' })

const authStore = useAuthStore()
const { getMyOrders } = useOrders()
const { getMyRentals } = useRentals()

const { data: orders } = await useAsyncData('my-orders', () => getMyOrders())
const { data: rentals } = await useAsyncData('my-rentals', () => getMyRentals())

const statutColor = (statut) => ({
  en_attente: 'warning',
  paye: 'success',
  reserve: 'info',
  en_cours: 'primary',
  retourne: 'neutral',
  annule: 'error',
}[statut] || 'neutral')

const genreLabel = computed(() => ({
  homme: 'Homme',
  femme: 'Femme',
  autre: 'Autre',
}[authStore.user?.genre] || 'Non renseigné')
)
</script>

<template>
  <div class="p-8">
    <!-- Carte profil -->
    <UCard class="mb-6">
      <div class="flex items-center gap-4">
        <UAvatar :src="authStore.user?.photo_url" :alt="authStore.user?.nom" size="xl" />
        <div class="flex-1">
          <h2 class="text-lg font-bold">{{ authStore.user?.nom }}</h2>
          <p class="text-sm text-gray-500">{{ authStore.user?.email }}</p>
        </div>
        <UButton variant="soft" size="sm" icon="i-lucide-pencil" to="/dashboard/profil">Modifier</UButton>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5 pt-5 border-t border-gray-200 dark:border-gray-800 text-sm">
        <div>
          <p class="text-gray-400 text-xs">Téléphone</p>
          <p class="font-medium">{{ authStore.user?.telephone || 'Non renseigné' }}</p>
        </div>
        <div>
          <p class="text-gray-400 text-xs">Adresse</p>
          <p class="font-medium">{{ authStore.user?.adresse || 'Non renseignée' }}</p>
        </div>
        <div>
          <p class="text-gray-400 text-xs">Genre</p>
          <p class="font-medium">{{ genreLabel }}</p>
        </div>
        <div>
          <p class="text-gray-400 text-xs">Membre depuis</p>
          <p class="font-medium">{{ new Date(authStore.user?.created_at).toLocaleDateString('fr-FR') }}</p>
        </div>
      </div>
    </UCard>

    <!-- Stats rapides -->
    <div class="grid grid-cols-2 gap-4 mb-6">
      <UCard>
        <p class="text-sm text-gray-500">Commandes</p>
        <p class="text-3xl font-bold mt-1">{{ orders?.data?.length ?? 0 }}</p>
      </UCard>
      <UCard>
        <p class="text-sm text-gray-500">Locations</p>
        <p class="text-3xl font-bold mt-1">{{ rentals?.data?.length ?? 0 }}</p>
      </UCard>
    </div>

    <UTabs :items="[{ label: 'Mes commandes', slot: 'orders' }, { label: 'Mes locations', slot: 'rentals' }]">
      <template #orders>
        <div v-if="orders?.data?.length" class="space-y-3 mt-4">
          <UCard v-for="order in orders.data" :key="order.id">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium">Commande #{{ order.id }}</p>
                <p class="text-sm text-gray-500">
                  {{ order.items?.length }} article(s) · {{ Number(order.total).toLocaleString('fr-FR') }} FCFA
                </p>
              </div>
              <UBadge :color="statutColor(order.statut)" variant="subtle">{{ order.statut }}</UBadge>
            </div>
          </UCard>
        </div>
        <p v-else class="text-gray-500 mt-4">Aucune commande pour le moment.</p>
      </template>

      <template #rentals>
        <div v-if="rentals?.data?.length" class="space-y-3 mt-4">
          <UCard v-for="rental in rentals.data" :key="rental.id">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium">{{ rental.product?.nom }}</p>
                <p class="text-sm text-gray-500">
                  Du {{ rental.date_debut }} au {{ rental.date_fin }} ·
                  {{ Number(rental.montant_total).toLocaleString('fr-FR') }} FCFA
                </p>
              </div>
              <UBadge :color="statutColor(rental.statut)" variant="subtle">{{ rental.statut }}</UBadge>
            </div>
          </UCard>
        </div>
        <p v-else class="text-gray-500 mt-4">Aucune location pour le moment.</p>
      </template>
    </UTabs>
  </div>
</template>