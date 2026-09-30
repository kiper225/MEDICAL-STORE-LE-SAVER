export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as null | { id: number; nom: string; role: string },
  }),
  actions: {
    async login(email: string, password: string) {
      const api = useApi()
      const token = useCookie('auth_token', {
        maxAge: 60 * 60 * 24 * 7, // 7 jours
        sameSite: 'lax',
      })
      const response = await api('/login', { method: 'POST', body: { email, password } })
      token.value = response.token
      this.user = response.user
    },
    async fetchUser() {
      if (!this.user) {
        const api = useApi()
        try {
          this.user = await api('/user')
        } catch {
          this.user = null
        }
      }
    },
    async logout() {
      const api = useApi()
      const token = useCookie('auth_token')
      await api('/logout', { method: 'POST' })
      token.value = null
      this.user = null
      navigateTo('/login')
    },
  },
})