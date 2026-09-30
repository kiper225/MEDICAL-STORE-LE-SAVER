<script setup>
const newsletterEmail = ref('')
</script>

<template>
  <footer class="bg-primary text-white mt-12">
    <!-- Bandeau avantages -->
    <div class="border-b border-white/10">
      <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
        <div>
          <p class="font-semibold">LIVRAISON RAPIDE</p>
          <p class="text-white/60 mt-1">48H maxi.</p>
        </div>
        <div>
          <p class="font-semibold">PAIEMENT EN LIGNE</p>
          <p class="text-white/60 mt-1">CB, Mobile Money</p>
        </div>
        <div>
          <p class="font-semibold">SUPPORT 24/7</p>
          <p class="text-white/60 mt-1">Service d'assistance illimité</p>
        </div>
        <div>
          <p class="font-semibold">100% FIABLE</p>
          <p class="text-white/60 mt-1">Produits certifiés conformes</p>
        </div>
      </div>
    </div>

    <!-- Newsletter -->
    <div class="max-w-7xl mx-auto px-4 py-8 flex flex-col md:flex-row items-center justify-between gap-4 border-b border-white/10">
      <div>
        <p class="font-semibold">Nouveau sur Medical Store ?</p>
        <p class="text-white/60 text-sm mt-1">Rejoignez notre communauté pour recevoir nos meilleures offres !</p>
      </div>
      <div class="flex gap-2 w-full md:w-auto">
        <UInput v-model="newsletterEmail" placeholder="Votre adresse email" class="flex-1 md:w-72" />
        <UButton color="warning">Valider</UButton>
      </div>
    </div>

    <!-- Colonnes -->
    <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-2 md:grid-cols-5 gap-8 text-sm">
      <div>
        <h4 class="font-semibold mb-3">MON COMPTE</h4>
        <ul class="space-y-2 text-white/70">
          <li><NuxtLink to="/register" class="hover:text-white">Créer un compte</NuxtLink></li>
          <li><NuxtLink to="/dashboard/client" class="hover:text-white">Mes commandes</NuxtLink></li>
          <li><NuxtLink to="/panier" class="hover:text-white">Mon panier</NuxtLink></li>
        </ul>
      </div>
      <div>
        <h4 class="font-semibold mb-3">ESPACE VENDEUR</h4>
        <ul class="space-y-2 text-white/70">
          <li><NuxtLink to="/register?role=vendeur" class="hover:text-white">Devenir vendeur</NuxtLink></li>
          <li><NuxtLink to="/dashboard/vendeur" class="hover:text-white">Mon espace</NuxtLink></li>
        </ul>
      </div>
      <div>
        <h4 class="font-semibold mb-3">SERVICES CLIENT</h4>
        <ul class="space-y-2 text-white/70">
          <li>Tél : +225 05 74 48 00 00</li>
          <li><NuxtLink to="/services" class="hover:text-white">Comment acheter ?</NuxtLink></li>
        </ul>
      </div>
      <div>
        <h4 class="font-semibold mb-3">LIENS UTILES</h4>
        <ul class="space-y-2 text-white/70">
          <li><NuxtLink to="/" class="hover:text-white">Qui sommes-nous ?</NuxtLink></li>
          <li><NuxtLink to="/" class="hover:text-white">Politique de confidentialité</NuxtLink></li>
          <li><NuxtLink to="/" class="hover:text-white">Nous contacter</NuxtLink></li>
        </ul>
      </div>
      <div>
        <h4 class="font-semibold mb-3">SUIVEZ-NOUS</h4>
        <div class="flex gap-2">
          <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 cursor-pointer">
            <UIcon name="i-simple-icons-facebook" class="w-4 h-4" />
          </div>
          <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 cursor-pointer">
            <UIcon name="i-simple-icons-x" class="w-4 h-4" />
          </div>
          <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 cursor-pointer">
            <UIcon name="i-simple-icons-whatsapp" class="w-4 h-4" />
          </div>
        </div>
      </div>
    </div>

    <div class="border-t border-white/10 text-center text-xs text-white/50 py-4">
      Medical Store Dieu Sauveur © {{ new Date().getFullYear() }}. Tous droits réservés.
    </div>
  </footer>
</template>