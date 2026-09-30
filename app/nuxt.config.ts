// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: false },
  modules: ['@pinia/nuxt', '@nuxt/ui'],
  css: ['~/assets/css/main.css'],

  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL || 'http://localhost:8000/api',
    },
  },
  ui: {
    colors: {
      primary: 'blue',
      neutral: 'slate',
    },
  },
})