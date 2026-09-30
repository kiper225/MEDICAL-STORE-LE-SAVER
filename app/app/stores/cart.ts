export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [] as { product: any; quantite: number }[],
  }),
  getters: {
    totalItems: (state) => state.items.reduce((sum, i) => sum + i.quantite, 0),
    totalPrice: (state) =>
      state.items.reduce((sum, i) => sum + (Number(i.product.prix_vente) || 0) * i.quantite, 0),
  },
  actions: {
    addItem(product, quantite = 1) {
      const existing = this.items.find((i) => Number(i.product.id) === Number(product.id))
      if (existing) {
        existing.quantite += quantite
      } else {
        this.items.push({ product, quantite })
      }
      this.persist()
    },
    updateQuantity(productId, quantite) {
      const item = this.items.find((i) => Number(i.product.id) === Number(productId))
      if (item) {
        item.quantite = Math.max(1, quantite)
        this.persist()
      }
    },
    removeItem(productId) {
      this.items = this.items.filter((i) => Number(i.product.id) !== Number(productId))
      this.persist()
    },
    clear() {
      this.items = []
      this.persist()
    },
    persist() {
      if (import.meta.client) {
        localStorage.setItem('cart', JSON.stringify(this.items))
      }
    },
    hydrate() {
      if (import.meta.client) {
        const saved = localStorage.getItem('cart')
        if (saved) this.items = JSON.parse(saved)
      }
    },
  },
})