<script setup>
const route = useRoute()
</script>

<template>
  <div>
    <AppHeader v-if="route.meta.layout !== 'dashboard'" />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
    <AppFooter v-if="route.meta.layout !== 'dashboard'" />
  </div>
</template>