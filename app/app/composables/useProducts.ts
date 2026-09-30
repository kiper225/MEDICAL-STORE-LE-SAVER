export const useProducts = () => {
  const api = useApi()

  const getAllProducts = () => api('/products', { params: { all: true } })
  const getProducts = (params = {}) => api('/products', { params })
  const getProduct = (slug: string) => api(`/products/${slug}`)
  const createProduct = (payload) => api('/products', { method: 'POST', body: payload })
  const updateProduct = (slug: string, payload) => api(`/products/${slug}`, { method: 'PUT', body: payload })
  const deleteProduct = (slug: string) => api(`/products/${slug}`, { method: 'DELETE' })
  const deleteProductImage = (productSlug, imageId) =>
    api(`/products/${productSlug}/images/${imageId}`, { method: 'DELETE' })
  const uploadImages = (productSlug, formData) =>
    api(`/products/${productSlug}/images`, { method: 'POST', body: formData })

  return { getProducts, getProduct, createProduct, updateProduct, deleteProduct, getAllProducts, deleteProductImage, uploadImages }
}