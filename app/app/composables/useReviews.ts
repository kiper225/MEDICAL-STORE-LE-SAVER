export const useReviews = () => {
  const api = useApi()

  const getReviews = (slug) => api(`/products/${slug}/reviews`)
  const submitReview = (slug, payload) =>
    api(`/products/${slug}/reviews`, { method: 'POST', body: payload })

  return { getReviews, submitReview }
}